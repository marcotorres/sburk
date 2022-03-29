<?php

namespace App\Http\Controllers\Profile;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Http\Helpers\Upload;
use Avatar;
use App\User;
use Validator;
use DB;
use App\Driver;
use App\Parent_;
use App\Plan;
use App\Setting;
use App\SettingType;

use App\Http\Traits\SwitchPlans;

class ProfileController extends Controller
{
    use SwitchPlans;

    public function __construct() {
        \Stripe\Stripe::setApiKey(Setting::where('name','Stripe Secret key')->first()->value);
    }
    /* get the current logged school */
    public function getAuthUser ()
    {
        $school = User::with('plan')->find(Auth::id());
        return $school;
    }
    
    /* update the attributes of the current logged school */
    public function updateAuthUser (Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email|unique:schools,email,'.Auth::id()
        ]);
        // get the current school
        $school = Auth::user();
        // update its attribute
        $school->name = $request->name;
        $school->email = $request->email;
        $school->save();
        // create an avatar image for the school account based on the school's name
        $avatar = Avatar::create($school->name)->getImageObject()->encode('png');
        Storage::put('avatars/'.$school->id.'/avatar.png', (string) $avatar);
        return $school;
    }
    /* update the password of the current logged school */
    public function updatePasswordAuthUser(Request $request)
    {
        
        $this->validate($request, [
            'current' => 'required|string',
            'password' => 'required|string|confirmed',
            'password_confirmation' => 'required|string'
        ]);
        // get the logged school
        $school = Auth::user();
        // make sure that the current password is correct
        if (!Hash::check($request->current, $school->password)) {
            return response()->json(['errors' => ['current'=> ['Current password does not match']]], 422);
        }
        // update the password and save
        $school->password = Hash::make($request->password);
        $school->save();
        return $school;
    }
    /* update the avatar image of the current school account */
    public function uploadAvatarAuthUser(Request $request)
    {
        $upload = new Upload();
        $avatar = $upload->upload($request->file, 'avatars/'.Auth::id())->resize(200, 200)->getData();

        $school = Auth::user();
        $school->avatar = $avatar['basename'];
        $school->save();

        return $school;
    }
    /* remove the avatar image of the current school account */
    public function removeAvatarAuthUser()
    {
        $school = Auth::user();
        $school->avatar = 'avatar.png';
        $school->save();
        return $school;
    }


    /* get specific plan */
    public function showPlan ($plan)
    {
        return [
            'plan' => Plan::findOrFail($plan), 
            "currency" => Setting::where('name', 'Currency')->first()->value,
            "billing_cycle" => Setting::where('name', 'Billing cycle')->first()->value
        ];
    }

    /* update the payment card of a school account */
    public function updatePayment (Request $request)
    {
        // get the request data and make sure that it contains the required information
        $requestData = $request->all();
        if (!(array_key_exists('paymentMethodId', $requestData) && array_key_exists('plan', $requestData)))
        {
            return response()->json(['errors' => ['Payment'=> ['Payment can not be created']]], 422);
        }
        // get the paymentMethodId and plan id sent from front end
        $paymentMethodId = $requestData['paymentMethodId'];
        $plan = $requestData['plan'];
        //get plan details
        $plan = Plan::findOrFail($plan);
        // get the current school
        $school = Auth::user();
        try {
            if ($school->subscribed('main')) {
                $school->subscription('main')->cancelNow();
                //return to free plan
                $free_plan = Plan::where('price',0)->first();
                $this->updatePlanAndAdjustLimit($school, $free_plan);
            }
            //dd($paymentMethodId);
            // subscribe school to plan
            $school->newSubscription('main', $plan->stripe_plan)->create($paymentMethodId);
            //update school's plan
            $this->updatePlanAndAdjustLimit($school, $plan);
          } catch (\Stripe\Error\Base $e) {
            return response()->json(['errors' => ['Payment'=> [$e->getMessage()]]], 422);
          } catch (\Exception $e) {
            return response()->json(['errors' => ['Payment'=> [$e->getMessage()]]], 422);
          }
    }
    
    /* update the current plan of a school account */
    public function updateSchoolPlan(Request $request)
    {
        //get the current school
        $school = Auth::user();
        if($school->is_super_admin_account)
        {
            //super admin want to change a school account's plan
            $school_account = User::findOrFail($request->id);
            if(!$school_account)
                return response()->json(['errors' => ['School'=> ['school can not be selected']]], 422);
            //get the current plan
            $plan = Plan::findOrFail($request->plan["id"]);
            if(!$plan)
                return response()->json(['errors' => ['Plan'=> ['plan can not be selected']]], 422);
            
            //$is_stripe_enabled = SettingType::where('name','Stripe')->first()->enabled;
            
            $this->updatePlanAndAdjustLimit($school_account, $plan);
        }
        else
        {
            
            //get the plan
            $plan = Plan::findOrFail($request->plan);
            
            if(!$plan)
                return response()->json(['errors' => ['Plan'=> ['plan can not be selected']]], 422);
            //get the current school
            $school = Auth::user();

            $is_stripe_enabled = SettingType::where('name','Stripe')->first()->enabled;
            if($is_stripe_enabled)
            {
                try {
                    if(!$school->stripe_id)
                        $school->createAsStripeCustomer();
                        
                    if ($plan->price==0) //if free plan is selected
                    {
                        //cancel the current subscription if exists
                        if($school->subscribed('main'))
                            $school->subscription('main')->cancelNow();
                        
                        //update school's plan
                        $this->updatePlanAndAdjustLimit($school, $plan);
                        return view('profile.plan');
                    }
                    else {
                        //otherwise, go to payment
                        $setting = Setting::where('name','Stripe Publishable key')->first();
                        return view('profile.pay', ['stripe_publishable_key' => $setting->value]);
                    }
                } catch (\Stripe\Error\Base $e) {
                    return response()->json(['errors' => ['Payment'=> [$e->getMessage()]]], 422);
                } catch (\Exception $e) {
                    return response()->json(['errors' => ['Payment'=> [$e->getMessage()]]], 422);
                }
            }
            else
            {
                if ($plan->price==0) //if free plan is selected
                {
                    //cancel the current subscription if exists
                    if($school->subscribed('main'))
                        $school->subscription('main')->cancelNow();
                    //update school's plan
                    $this->updatePlanAndAdjustLimit($school, $plan);
                    return view('profile.plan');
                }
                else
                {
                    //\Stripe\Plan::retrieve('plan_GITYi90MA6yrVV');
                    return view('profile.plan');
                    //return response()->json(['errors' => ['Payment'=> 'aa']], 500);
                }
            }
        }
    }
    

}
