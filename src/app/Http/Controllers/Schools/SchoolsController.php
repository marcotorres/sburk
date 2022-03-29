<?php

namespace App\Http\Controllers\Schools;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Avatar;
use App\User;
use App\Setting;
use App\Http\Traits\CheckStripe;
use App\Plan;
use App\SettingType;
use DB;
/* this controller is used with super admin account */
class SchoolsController extends Controller
{
    use CheckStripe;


    /* show all schools*/
    public function showIndex (Request $request)
    {
        $can_create = false;
        $is_stripe_enabled = SettingType::where('name','Stripe')->first()->enabled;
        if($is_stripe_enabled)
        {
            $can_create = $this->is_stripe_ok();
        }
        else
            $can_create = true;
        
        return view('schools.index', ['can_create' => $can_create]);
    }

    /* get all schools based on filter */
    public function filter (Request $request)
    {
        $query = User::query();
        $query->with('plan')->where('is_super_admin_account', 0);
        //get schools based on search term if any
        if($request->search) {
            $query->where('name', 'LIKE', '%'.$request->search.'%');
        }
        // sort the obtained schools
        $schools = $query->orderBy($request->input('orderBy.column'), $request->input('orderBy.direction'))
                    ->paginate($request->input('pagination.per_page'));

        return [
            'plans' => Plan::orderBy('price')->get(), 
            'schools' => $schools, 
            "currency" => Setting::where('name', 'Currency')->first()->value,
            "billing_cycle" => Setting::where('name', 'Billing cycle')->first()->value
        ];
    }
    /* get specific school */
    public function show ($school)
    {
        return User::findOrFail($school);
    }
    /* update a specific school's data */
    public function update (Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,'.$request->id,
            'password' => 'string|nullable',
        ]);

        $user = User::find($request->id);

        if ($user->name != $request->name) {
            $avatar = Avatar::create($request->name)->getImageObject()->encode('png');
            Storage::put('avatars/'.$user->id.'/avatar.png', (string) $avatar);
            $user->name = $request->name;
        }
        if ($user->email != $request->email) {
            $user->email = $request->email;
        }
        if ($request->password != '') {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return $user;
    }
    /* delete a school account */
    public function destroy ($user)
    {        
        $school = User::with('drivers', 'parents', 'buses')->find($user);
        $is_stripe_enabled = SettingType::where('name','Stripe')->first()->enabled;
        if($is_stripe_enabled)
        {
            //delete stripe subscription as well
            $stripe_key = Setting::where('name','Stripe Secret key')->first()->value;
            if($stripe_key)
            {
                if ($school->subscribed('main')) {
                    $school->subscription('main')->cancelNow();
                }
            }
        }
        // Start transaction!
        DB::beginTransaction();
        try 
        {
            if($school->parents)
            {
                foreach ($school->parents as $key => $parent) {
                    $parent->forceDelete();
                }
            }
            if($school->drivers)
            {
                foreach ($school->drivers as $key => $driver) {
                    $driver->forceDelete();
                }
            }
            if($school->buses)
            {
                foreach ($school->buses as $key => $bus) {
                    $bus->forceDelete();
                }
            }
            $school->forceDelete();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['errors' => ['Error'=> [$e->getMessage()]]], 422);
        }
        // If we reach here, then data is valid and working.
        // Commit the queries!
        DB::commit();
    }

    /* create a new school */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email|unique:schools,email,',
            'password' => 'required|string'
        ]);

        $school = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'channel' => uniqid(),
            'password' => Hash::make($request->password),
        ]);

        try {
            $avatar = Avatar::create($school->name)->getImageObject()->encode('png');
            Storage::put('avatars/'.$school->id.'/avatar.png', (string) $avatar);
        }
        catch (\Exception $e) {

        }
        $is_stripe_enabled = SettingType::where('name','Stripe')->first()->enabled;
        if($is_stripe_enabled)
        {
            //create stripe customer as well
            $stripe_key = Setting::where('name','Stripe Secret key')->first()->value;
            if($stripe_key)
            {
                \Stripe\Stripe::setApiKey($stripe_key);
                $school->createAsStripeCustomer();
            }
        }
        return $school;
    }
}
