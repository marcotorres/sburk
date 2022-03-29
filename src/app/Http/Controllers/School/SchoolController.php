<?php

namespace App\Http\Controllers\School;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

use App\Http\Helpers\Upload;

use App\User;
use App\Setting;

class SchoolController extends Controller
{
    /* get the index view for the current logged school account */
    public function index()
    {
        $setting = Setting::where('name','Google maps API key')->first();
        return view('school.school', ['GOOGLE_MAPS_API_KEY' => $setting->value]);
    }
    /* get the current logged school */
    public function getSchool()
    {
        $school = Auth::user();
        return json_encode($school);
    }
    /* update the attributes of the current school */
    public function updateSchool (Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'address' => 'required|string',
            'latitude' => 'required',
            'longitude' => 'required'
        ]);

        $school = Auth::user();
        $school->name = $request->name;
        $school->address = $request->address;
        $school->latitude = $request->latitude;
        $school->longitude = $request->longitude;
        $school->save();
        return $school;
    }
}
