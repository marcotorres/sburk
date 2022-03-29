<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\User;
use App\Setting;
use Validator;
use App\SettingType;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Auth::user()->is_super_admin_account)
            return view('sadmin_dashboard');
        else
            return view('dashboard');
    }
    /* make super admin to log in as school admin */
    public function loginAsSchoolAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'school_id' => 'required|numeric']);
        
        if ($validator->fails()) {
            //pass validator errors as errors object for ajax response
            return response()->json(['errors'=>$validator->errors()], 422);
        }
        $school_account = User::findOrFail($request->school_id);
        if($school_account)
        {
            Auth::logout();
            Auth::loginUsingId($request->school_id);
            return response()->json(['success' => ['ok']]);
        }
        else
            return response()->json(['errors' => ['school not exists']], 404);
    }
    /* get drivers and parents data to be displayed in dashboard for school admin */
    public function getParentsDriversInfo()
    {
        // get the current logged school
        $school = Auth::user();
        // get the count of registered drivers for the school
        $drivers_count = $school->drivers->count();
        // get the count of registered drivers but not verified yet
        $verified_drivers_count = $school->drivers->where('verified', 1)->count();
        // get the count of registered parents for the school
        $parents_count = $school->parents->count();
        // get the count of registered parents but not verified yet
        $verified_parents_count = $school->parents->where('verified', 1)->count();
        // create a ParentsDriversInfo object with the above data
        $ParentsDriversInfo = ["drivers_count" => $drivers_count, 
                                "parents_count" => $parents_count,
                                "verified_drivers_count" => $verified_drivers_count,
                                "not_verified_drivers_count" => $drivers_count - $verified_drivers_count,
                                "verified_parents_count" => $verified_parents_count,
                                "not_verified_parents_count" => $parents_count - $verified_parents_count];
        return $ParentsDriversInfo;
    }
    /* get registered schools accounts data to be displayed in dashboard for super admin */
    public function getSchoolsInfo()
    {
        //get schools count grouped by their plans
        $schools = User::with('plan')->groupBy('plan_id')
                        ->selectRaw('count(*) as total, plan_id')
                        ->where('is_super_admin_account', 0)->get();

        return [ 
            'schools' => $schools, 
            "currency" => Setting::where('name', 'Currency')->first()->value,
        ];
        return $schools;
    }

    /* get stripe balance data to be displayed in dashboard for super admin */
    public function getStripeBalance()
    {
        $is_stripe_enabled = SettingType::where('name','Stripe')->first()->enabled;
        if($is_stripe_enabled)
        {
            \Stripe\Stripe::setApiKey(Setting::where('name','Stripe Secret key')->first()->value);
            $balance = \Stripe\Balance::retrieve();
            //$balance = \Stripe\BalanceTransaction::all();
            //dd($balance);
            return $balance;
        }
        else
            return 0;

    }

}
