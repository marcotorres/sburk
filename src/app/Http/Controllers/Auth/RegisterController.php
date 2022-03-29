<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Storage;

use Avatar;
use App\Setting;
use App\SettingType;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new schools as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255|unique:schools',
            'email' => 'required|string|email|max:255|unique:schools',
            'password' => 'required|string|min:6|confirmed',
        ]);
    }

    /**
     * Create a new school instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $school = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'channel' => uniqid(),
            'password' => Hash::make($data['password']),
        ]);

        $avatar = Avatar::create($school->name)->getImageObject()->encode('png');
        Storage::put('avatars/'.$school->id.'/avatar.png', (string) $avatar);
        
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
