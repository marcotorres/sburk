<?php

namespace App\Http\Controllers\Drivers;

use App\Driver;
use App\Parent_;
use App\Child;
use App\DriverChildCheckInOut;
use App\DriverParentVisit;
use App\DriverSchoolVisit;
use App\DriverZoneVisit;
use App\Http\Controllers\SmsController;
use App\Setting;
use App\SettingType;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Validator;
use DB;

class DriverController extends SmsController
{
    /* firebase account to send notifications to the mobile app */
    public $firebase;

    static $bus_arrived = 1;
    static $bus_left = 2;

    static $maxDistance = 50; //in meteres

    /**
     * Calculates the great-circle distance between two points, with
     * the Haversine formula.
     * @param float $latitudeFrom Latitude of start point in [deg decimal]
     * @param float $longitudeFrom Longitude of start point in [deg decimal]
     * @param float $latitudeTo Latitude of target point in [deg decimal]
     * @param float $longitudeTo Longitude of target point in [deg decimal]
     * @param float $earthRadius Mean earth radius in [m]
     * @return float Distance between points in [m] (same as earthRadius)
     */
    private function getDistance(
        $latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000) {
        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);
        // compute the lat and lng differences
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
        // compute the distance using Haversine formula
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }
    /* constructor */
    public function __construct()
    {
        //initialize the firebase account
        $this->firebase = (new Factory)
            ->withServiceAccount('./fcm.json')
            ->create();
    }

    /* get the map view*/
    public function showmap()
    {
        $setting = Setting::where('name', 'Google maps API key')->first();
        return view('drivers.map', ['GOOGLE_MAPS_API_KEY' => $setting->value]);
    }

    /* show all drivers on the map view*/
    public function showAllMap()
    {
        $setting = Setting::where('name', 'Google maps API key')->first();
        return view('drivers.map', ['GOOGLE_MAPS_API_KEY' => $setting->value]);
    }

    /* ***************************** Web APIs ********************************/

    /* get all drivers based on filter */
    public function filter(Request $request)
    {
        // get the current logged school
        $school = Auth::user();
        // get all drivers with names that match the filter term
        $query = Driver::query();
        $query->where('school_id', $school->id);
        if ($request->search) {
            $query->where('name', 'LIKE', '%' . $request->search . '%');
        }
        // order the obtained drivers with the requred order column
        $drivers = $query->orderBy($request->input('orderBy.column'), $request->input('orderBy.direction'))
            ->paginate($request->input('pagination.per_page'));
        //load buses data with the obtained drivers
        $drivers->load('bus');
        //return the obtained drivers
        return $drivers;
    }
    /* get all drivers in school account */
    public function all()
    {
        // get the current logged school
        $school = Auth::user();
        // get all drivers within the school account
        $query = Driver::query();
        return $query->with('school')->where('school_id', $school->id)->get();
    }
    /* get a specific driver */
    public function show($driver)
    {
        return Driver::findOrFail($driver->id);
    }
    /* get the event log of specific driver */
    public function getLog(Request $request, $driver)
    {
        $event_type = $request->get('event_type');
        $select_value = $request->get('select_value');
        $start_date = json_decode($request->get('start_date'));
        $end_date = json_decode($request->get('end_date'));

        $start_date = Carbon::parse($start_date->date)->toDateString();
        $end_date = Carbon::parse($end_date->date)->addDays(1)->toDateString();

        if ($event_type == "All") {
            $event_type = 0;
        } else if ($event_type == "Arrive") {
            $event_type = 1;
        } else if ($event_type == "Leave") {
            $event_type = 2;
        } else if ($event_type == "Child check-in") {
            $event_type = 3;
        } else if ($event_type == "Child check-out") {
            $event_type = 4;
        }

        //select_types: ['All', 'School', 'Parents', 'Bus'],
        $driver_school_visits = null;
        $driver_parent_visits = null;
        $driver_children_checks = null;

        if($event_type == 0 || $event_type == 1 || $event_type == 2)
        {
            if ($select_value == "School" || $select_value == "All") {
                $driver_school_visits = DriverSchoolVisit::visits($start_date, $end_date, $event_type)
                    ->select(DB::raw("id, case_id, updated_at, '1' AS event_place"))
                    ->where('driver_id', $driver->id)->get();
            }
            if ($select_value == "Parents" || $select_value == "All") {
                $driver_parent_visits = DriverParentVisit::with('parent')->visits($start_date, $end_date, $event_type)
                ->select(DB::raw("id, parent_id, case_id, updated_at, '2' AS event_place"))
                    ->where('driver_id', $driver->id)->get();
            }
        }
        if($event_type == 0 || $event_type == 3 || $event_type == 4)
        {
            if ($select_value == "Bus" || $select_value == "All") {
                $driver_children_checks = DriverChildCheckInOut::with('child')->checks($start_date, $end_date, $event_type)
                ->select(DB::raw("id, child_id, case_id, updated_at, '3' AS event_place"))
                    ->where('driver_id', $driver->id)->get();
            }
        }

        //dd($driver_school_visits, $driver_parent_visits, $driver_children_checks);
        if ($select_value == "All") {
            $all = $driver_school_visits;
            if($all == null)
            {
                $all = collect();
            }
            if($driver_parent_visits)
            {
                $all = $all->concat($driver_parent_visits);
            }
            if($driver_children_checks)
            {
                $all = $all->concat($driver_children_checks);
            }
            $all = $all->sortBy('updated_at')->values()->all();
            return $all;
        }
        if ($select_value == "School") {
            return $driver_school_visits->sortBy('updated_at')->values()->all();
        }
        if ($select_value == "Parents") {
            return $driver_parent_visits->sortBy('updated_at')->values()->all();
        }
        if ($select_value == "Bus") {
            return $driver_children_checks->sortBy('updated_at')->values()->all();
        }
    }

    /* create a new driver */
    public function store(Request $request)
    {
        // get the telephone number of the new driver
        $original_tel_number = $request->tel_number;
        // make nice names for validation errors
        $niceNames = [
            'tel_number' => 'telephone number',
            'bus_id' => 'bus',
        ];
        //validate the request
        $this->validate($request, [
            'name' => 'required|string',
            'country_code' => 'required|numeric',
            'tel_number' => 'required|numeric|unique:drivers,tel_number',
        ], [], $niceNames);
        // get request data
        $requestData = $request->all();
        // create a request data that contains the bus id of the new driver
        if ($requestData['bus']) {
            $requestData['bus_id'] = (int) $requestData['bus']['id'];
        }
        // augment the telephone number field in the request with the country code
        if (array_key_exists('tel_number', $requestData) && array_key_exists('country_code', $requestData)) {
            $requestData['tel_number'] = $requestData['country_code'] . $requestData['tel_number'];
        }
        // update the request
        $request->replace($requestData);
        // validate the request for bus id and for uniqueness of telephone number after
        //augmenting with country code
        $this->validate($request, [
            'bus_id' => 'required|numeric|unique:drivers',
            'tel_number' => 'unique:drivers|unique:parents',
        ], [], $niceNames);
        // get the current logged school
        $school = Auth::user();
        //create the driver
        $driver = Driver::create([
            'name' => $request->name,
            'channel' => uniqid(),
            'secretKey' => uniqid(),
            'bus_id' => $request->bus_id,
            'country_code' => $request->country_code,
            'tel_number' => $original_tel_number,
            'school_id' => $school->id,
        ]);
        return $driver;
    }
    /* update a specific driver */
    public function update(Request $request)
    {
        // get the required driver to be updated
        $driver = Driver::find($request->id);
        //if the driver found
        if ($driver) {
            // make nice names for validation errors
            $niceNames = [
                'tel_number' => 'telephone number',
                'bus_id' => 'bus',
            ];
            //validate the request
            $this->validate($request, [
                'name' => 'required|string',
                'country_code' => 'required|numeric',
                'tel_number' => 'required|numeric|unique:drivers,tel_number',
            ], [], $niceNames);
            // get the telephone number of the new driver
            $original_tel_number = $request->tel_number;
            // get request data
            $requestData = $request->all();
            // create a request field that contains the bus id of the new driver
            if ($requestData['bus']) {
                $requestData['bus_id'] = (int) $requestData['bus']['id'];
            }
            // augment the telephone number field in the request with the country code
            if (array_key_exists('tel_number', $requestData) && array_key_exists('country_code', $requestData)) {
                $requestData['tel_number'] = $requestData['country_code'] . $requestData['tel_number'];
            }
            // update the request
            $request->replace($requestData);
            // validate the request for bus id and for uniqueness of telephone number after
            //augmenting with country code
            $this->validate($request, [
                'bus_id' => ['required', 'numeric', Rule::unique('drivers')->ignore($request->id)],
                'tel_number' => [Rule::unique('drivers')->ignore($request->id), Rule::unique('parents')],
            ], [], $niceNames);
            // get the current logged school
            $school = Auth::user();
            if ($driver->school_id != $school->id) {
                return response()->json(['errors' => ['Driver' => ['driver is not belonging to this school']]], 403);
            }
            //update the driver
            $driver->name = $request->name;
            $driver->country_code = $request->country_code;
            $driver->tel_number = $original_tel_number;
            $driver->school_id = $school->id;
            $driver->bus_id = $request->bus_id;
            $driver->verified = $request->verified;
            $driver->save();
        }
    }
    /* delete a specific driver */
    public function destroy($driver)
    {
        $driver->forceDelete();
    }
    /* get a specific driver with associated bus, school, and assigned parents*/
    public function getDriver($driver)
    {
        return Driver::with('bus', 'parents', 'school')->findOrFail($driver->id);
    }

    /* ***************************** Mobile APIs ********************************/

    /* get a driver information with school and bus data using his telephone number */
    public function getSchoolBusDriverTelNumber(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'secretKey' => 'required',
            'country_code' => 'required',
            'tel_number' => 'required|numeric']);

        if ($validator->fails()) {
            //pass validator errors as errors object for ajax response
            return response()->json(['errors' => $validator->errors()], 422);
        }
        // get the driver based on request data
        $query = Driver::query();
        $query->where('tel_number', $request->tel_number);
        $query->where('country_code', $request->country_code);
        $driver = $query->where('secretKey', $request->secretKey)->first();
        if ($driver) {
            $respDriver = Driver::with(['bus', 'school', 'parents.children'])->findOrFail($driver->id);
            // order parents based on their locations with driver

            for ($i=0; $i < count($respDriver->parents); $i++) {
                if($respDriver->last_latitude ==null || $respDriver->last_longitude == null || 
                $respDriver->parents[$i]->address_latitude == null || 
                $respDriver->parents[$i]->address_longitude == null)
                {
                    $respDriver->parents[$i]->driverDist = 99999999;
                }
                else
                {
                    $respDriver->parents[$i]->driverDist = $this->getDistance($respDriver->last_latitude, 
                    $respDriver->last_longitude, $respDriver->parents[$i]->address_latitude, 
                    $respDriver->parents[$i]->address_longitude);
                }
            }

            for ($i=0; $i < count($respDriver->parents); $i++) { 
                for ($j=$i+1; $j < count($respDriver->parents); $j++) { 
                    if($respDriver->parents[$i]->driverDist > $respDriver->parents[$j]->driverDist)
                    {
                        // swap
                        $tmp = $respDriver->parents[$i];
                        $respDriver->parents[$i] = $respDriver->parents[$j];
                        $respDriver->parents[$j] = $tmp;
                    }
                }
            }
            return response()->json(['driver' => $respDriver]);
        } else {
            return response()->json(['errors' => ['id' => ['driver not exists']]], 404);
        }

    }
    /* authenticate a driver using his telephone number. This function is called
    when the driver sign ups for the first time on mobile device */
    public function authDriverTelNumber(Request $request)
    {
        $niceNames = [
            'tel_number' => 'telephone number',
        ];

        $validator = Validator::make($request->all(), [
            'country_code' => 'required',
            'tel_number' => 'required|numeric'], [], $niceNames);

        if ($validator->fails()) {
            //pass validator errors as errors object for ajax response
            return response()->json(['errors' => $validator->errors()], 422);
        }
        // get the driver based on his telephone number
        $query = Driver::query();
        $driver = $query->where('tel_number', $request->tel_number)
            ->where('country_code', $request->country_code)->first();
        //if driver exists
        if ($driver) {
            // generate random verification code and save it to the driver record
            $pin = mt_rand(100000, 999999);
            // $driver->v_code = 111111;
            $driver->v_code = $pin;
            // clear the verified field in the driver record
            $driver->verified = 0;
            //check if the super admin enabled sms verification
            $is_sms_enabled = SettingType::where('name', 'SMS')->first()->enabled;
            if ($is_sms_enabled) {
                $system_name = Setting::where('name', 'System name')->first()->value;
                //send v_code via OTP
                $messageBody = $driver->v_code . " is your verification code from ". $system_name;
                $recieverTelNumber = "+" . $driver->country_code . $driver->tel_number;
                $this->sendSms($messageBody, $recieverTelNumber);
            }
            // send success
            $driver->save();
            return response()->json(['success' => ['verification code sent successfully']]);
        } else {
            return response()->json(['errors' => ['driver not exists']], 404);
        }

    }
    /* verifies a driver using his tel number on mobile device */
    public function verifyDriverTelNumber(Request $request)
    {
        $niceNames = [
            'tel_number' => 'telephone number',
            'v_code' => 'verification code',
        ];
        $validator = Validator::make($request->all(), [
            'country_code' => 'required',
            'tel_number' => 'required|numeric',
            'v_code' => 'required|numeric',
        ], [], $niceNames);
        if ($validator->fails()) {
            //pass validator errors as errors object for ajax response
            return response()->json(['errors' => $validator->errors()], 422);
        }
        // get the driver using his telephone number and verification code
        $query = Driver::query();
        $driver = $query->where('tel_number', $request->tel_number)
            ->where('country_code', $request->country_code)
            ->where('v_code', $request->v_code)->first();

        //if his telephone number and verification code matched
        if ($driver) {
            // set the verified field in the driver record
            $driver->verified = 1;
            // send success
            $driver->save();
            return response()->json(['driver' => Driver::with(['bus', 'school', 'parents'])
                    ->findOrFail($driver->id)]);
        } else {
            return response()->json(['errors' => ['verification code is not correct']], 422);
        }

    }
    /* update the current location of a driver */
    public function updatePosition(Request $request)
    {
        // try to find the driver based on his id
        $driver = Driver::with(['parents', 'school', 'latest_school_visit',
            'latest_parents_visit', 'latest_zones_visit'])->find($request->id);
        //if found
        if ($driver) {
            // make nice names for validation
            $niceNames = [
                'secretKey' => 'authentication',
                'last_latitude' => 'latitude',
                'last_longitude' => 'longitude',
            ];
            // validate the request
            $validator = Validator::make($request->all(), [
                'secretKey' => 'required',
                'last_latitude' => 'required|numeric',
                'last_longitude' => 'required|numeric',
            ], [], $niceNames);
            // if validation errors
            if ($validator->fails()) {
                // pass validator errors as errors object for ajax response
                return response()->json(['errors' => $validator->errors()], 422);
            }
            // if the secretKey does not match with the driver's one
            if ($driver->secretKey != $request->secretKey) {
                // pass error for ajax response
                return response()->json(['errors' => ['secretKey' => ['secret key does not match']]], 404);
            }
            // compute the distance travelled by the driver
            $dist = $this->getDistance($request->last_latitude, $request->last_longitude,
                $driver->last_latitude, $driver->last_longitude);

            // update the last location of the driver
            $driver->last_latitude = $request->last_latitude;
            $driver->last_longitude = $request->last_longitude;
            $driver->save();
            //form and send appropriate notification to parents
            $this->formNotification($driver);
            // broadcast the new location of the driver so all registered parents recieved the new location
            //and the app display it on Google map.
            $pos = array('id' => $driver->id,
                'distance' => $dist,
                'time' => round(microtime(true) * 1000),
                'lat' => $driver->last_latitude,
                'lng' => $driver->last_longitude);
            broadcast(new \App\Events\LocationChangeEvent($driver->channel, json_encode($pos)));
            //send the success ajax response
            return response()->json(['success' => ['driver location updated successfully']]);
        } else {
            return response()->json(['errors' => ['id' => ['driver not exists']]], 404);
        }

    }

    /* update the current location of a driver */
    public function updatePositionWithSpeed(Request $request)
    {
        // try to find the driver based on his id
        $driver = Driver::with(['parents', 'school', 'latest_school_visit',
            'latest_parents_visit', 'latest_zones_visit'])->find($request->id);
        //if found
        if ($driver) {
            // make nice names for validation
            $niceNames = [
                'secretKey' => 'authentication',
                'last_latitude' => 'latitude',
                'last_longitude' => 'longitude',
            ];
            // validate the request
            $validator = Validator::make($request->all(), [
                'secretKey' => 'required',
                'last_latitude' => 'required|numeric',
                'last_longitude' => 'required|numeric',
                'speed' => 'required|numeric',
            ], [], $niceNames);
            // if validation errors
            if ($validator->fails()) {
                // pass validator errors as errors object for ajax response
                return response()->json(['errors' => $validator->errors()], 422);
            }
            // if the secretKey does not match with the driver's one
            if ($driver->secretKey != $request->secretKey) {
                // pass error for ajax response
                return response()->json(['errors' => ['secretKey' => ['secret key does not match']]], 404);
            }
            // compute the distance travelled by the driver
            $dist = $this->getDistance($request->last_latitude, $request->last_longitude,
                $driver->last_latitude, $driver->last_longitude);

            // update the last location of the driver
            $driver->last_latitude = $request->last_latitude;
            $driver->last_longitude = $request->last_longitude;
            $driver->save();
            //form and send appropriate notification to parents
            $this->formNotification($driver);
            $speed = $request->speed;
            // broadcast the new location of the driver so all registered parents recieved the new location
            //and the app display it on Google map.
            $pos = array('id' => $driver->id,
                'distance' => $dist,
                'time' => round(microtime(true) * 1000),
                'lat' => $driver->last_latitude,
                'lng' => $driver->last_longitude,
                'speed' => $speed);
            broadcast(new \App\Events\LocationChangeEvent($driver->channel, json_encode($pos)));
            //send the success ajax response
            return response()->json(['success' => ['driver location updated successfully']]);
        } else {
            return response()->json(['errors' => ['id' => ['driver not exists']]], 404);
        }

    }
    /* Send message to the parents assigned to a driver */
    public function sendMessage(Request $request)
    {
        // get the current logged school
        $school = Auth::user();

        //get data from request
        $all = $request->all();
        if (!array_key_exists("params", $all)) {
            return response()->json(['errors' => ['Bad request']], 500);
        }

        $all = $all["params"];

        if (!array_key_exists("driver_id", $all) || !array_key_exists("message", $all)) {
            return response()->json(['errors' => ['Bad request']], 500);
        }

        $driver_id = $all["driver_id"];
        $message = $all["message"];

        if ($driver_id == null) {
            return response()->json(['errors' => ['No driver selected']], 500);
        }

        $driver = Driver::with('parents', 'school')->findOrFail($driver_id);
        //check if the driver is in the school account
        if ($driver->school->id != $school->id) {
            return response()->json(['errors' => ['Driver is not belonging to school']], 500);
        }

        // get the mobile devices tokens of the parents
        $deviceTokens = $driver->parents->pluck('fcm_token')->toArray();
        $messageType = "admin_message";
        // get the current server's time with time zone to be displayed with notification
        $timezone = config('app.timezone');
        $current_datetime = date('Y-m-d H:i:s') . ' ' . $timezone;

        // send the message
        $this->sendMultiFireBaseNotification($message, $current_datetime, $messageType, $deviceTokens);
        return response()->json(['success' => ['Message sent successfully']]);
    }

    /* form and send appropriate notification to parents based on the bus location */
    private function formNotification($driver)
    {
        // get the current server's time with time zone to be displayed with notification
        $timezone = config('app.timezone');
        $current_datetime = date('Y-m-d H:i:s') . ' ' . $timezone;
        // check if the driver arrived at or left school
        $school_vist_status = $this->checkDriverLocationSchool($driver);
        // if the driver has (left or arrived at) school status
        if ($school_vist_status) {
            //send notifications to parents with the status of the bus (left, arrived at) with school
            $this->formSchoolStatusNotification($driver, $school_vist_status, $current_datetime);
        }
        // if the driver does not (left or arrived at) school,
        //check if the driver (arrived at or left) a parent home
        else {
            //check the status with every parent's home
            foreach ($driver->parents as $parent) {
                $parent_vist_status = $this->checkDriverLocationParent($driver, $parent);
                // if the driver (arrived at or left) a parent home
                if ($parent_vist_status) {
                    //send notifications to a parent if the bus (left, arrived at) his home
                    $this->formParentStatusNotification($parent, $parent_vist_status, $current_datetime);
                    break;
                }
                // check if the driver near a parent home
                else {
                    $parent_zone_vist_status = $this->checkDriverLocationParentZone($driver, $parent);
                    if ($parent_zone_vist_status && $parent_zone_vist_status->case_id == $this::$bus_arrived) {
                        //send notifications to a parent if the bus near his home
                        $this->formParentZoneNotification($parent, $current_datetime);
                        break;
                    }
                }
            }
        }
    }
    /* form and send a notification that says that the bus arrived at or left school */
    private function formSchoolStatusNotification($driver, $school_vist_status, $current_datetime)
    {
        $deviceTokens = [];
        $arrivalLeaveStatus = "";
        $messageType = "";
        // if the driver arrived at school
        if ($school_vist_status->case_id == $this::$bus_arrived) {
            $arrivalLeaveStatus = "arrived at";
            $messageType = "arrived_school_notify";
            // get the mobile devices tokens of the parents
            $deviceTokens = $driver->parents->where('arrived_school', '1')->pluck('fcm_token')->toArray();
        }
        // if the driver left school
        else {
            $arrivalLeaveStatus = "left";
            $messageType = "left_school_notify";
            $deviceTokens = $driver->parents->where('left_school', '1')->pluck('fcm_token')->toArray();
        }
        //form the notification message
        $message_content = "Bus " . $arrivalLeaveStatus . " school";
        // send the message
        $this->sendMultiFireBaseNotification($message_content, $current_datetime, $messageType, $deviceTokens);
    }
    /* form and send a notification that says that the bus arrived at or left a parent's home */
    private function formParentStatusNotification($parent, $parent_vist_status, $current_datetime)
    {
        $arrivalLeaveStatus = "";
        $messageType = "";
        $sendNotification = false;
        if ($parent_vist_status->case_id == $this::$bus_arrived) {
            $arrivalLeaveStatus = "arrived at";
            $messageType = "arrived_home_notify";
            if ($parent->arrived_home == '1') {
                $sendNotification = true;
            }

        } else {
            $arrivalLeaveStatus = "left";
            $messageType = "left_home_notify";
            if ($parent->left_home == '1') {
                $sendNotification = true;
            }

        }
        if ($sendNotification) {
            $message_content = "Bus " . $arrivalLeaveStatus . " your home";
            $this->sendSingleFireBaseNotification($message_content, $current_datetime, $messageType, $parent->fcm_token);
        }
    }
    /* form and send a notification that says that the bus near a parent's home */
    private function formParentZoneNotification($parent, $current_datetime)
    {
        $message_content = "Get ready! Bus will reach your home soon";
        $messageType = "near_home";
        $this->sendSingleFireBaseNotification($message_content, $current_datetime, $messageType, $parent->fcm_token);
    }
    /* helper function to send a single firebase notification */
    private function sendSingleFireBaseNotification($message_content, $current_datetime, $messageType, $token)
    {
        if ($token == null) {
            return;
        }

        // create a firebase notification
        $messaging = $this->firebase->getMessaging();
        $message = CloudMessage::fromArray([
            'token' => $token,
            'data' => [
                "message_content" => $message_content,
                "time" => $current_datetime,
                "notification_type" => $messageType],
            'apns' => [
                'headers' => [
                    'apns-priority' => '10',
                ],
                'payload' => [
                    'aps' => [
                        'sound' => 'default',
                        'alert' => [
                            'body' => $message_content,
                        ],
                    ],
                ],
            ],
        ]);
        //send the notification
        $messaging->send($message);
    }
    /* helper function to send a multiple firebase notifications at once */
    private function sendMultiFireBaseNotification($message_content, $current_datetime, $messageType, $tokens)
    {
        if ($tokens == null) {
            return;
        }

        // create a firebase notification
        $messaging = $this->firebase->getMessaging();
        $message = CloudMessage::fromArray([
            'data' => [
                "message_content" => $message_content,
                "time" => $current_datetime,
                "notification_type" => $messageType],
            'apns' => [
                'headers' => [
                    'apns-priority' => '10',
                ],
                'payload' => [
                    'aps' => [
                        'sound' => 'default',
                        'alert' => [
                            'body' => $message_content,
                        ],
                    ],
                ],
            ],
        ]);

        // print_r($message);
        // print_r($tokens);
        //send the notification to multiple devices
        $messaging->sendMulticast($message, $tokens);
    }
    /* get the visit status of a driver with a parent's home. The function
    returns a DriverParentVisit record if the driver arrived at or left parent's home*/
    private function checkDriverLocationParent($driver, $parent)
    {
        //if the location of the parent is not set, return null
        if ($parent->address_latitude == null || $parent->address_longitude == null) {
            return null;
        }

        // compute the distance between the driver and parent's home
        $dist = $this->getDistance($driver->last_latitude, $driver->last_longitude,
            $parent->address_latitude, $parent->address_longitude);
        // get the last parent visit status
        $latest_parent_visit = $driver->latest_parents_visit->firstWhere('parent_id', $parent->id);
        // create a new DriverParentVisit record
        $parent_visit_status = new DriverParentVisit;
        $parent_visit_status->driver_id = $driver->id;
        $parent_visit_status->parent_id = $parent->id;
        //check if driver near parent home
        if ($dist < $this::$maxDistance) {
            //if so, check if we do not send "arrived at" home notification
            if ($latest_parent_visit == null ||
                $latest_parent_visit->case_id != $this::$bus_arrived) {
                $parent_visit_status->case_id = $this::$bus_arrived;
            }
        } else {
            //driver is far from parent home, check if we do not send "left" home notification
            if ($latest_parent_visit == null ||
                $latest_parent_visit->case_id != $this::$bus_left) {
                $parent_visit_status->case_id = $this::$bus_left;
            }
        }
        //if there is a visit status ("arrived at" or "left") home
        if ($parent_visit_status->case_id) {
            //save the DriverParentVisit record
            $parent_visit_status->save();
            //return $latest_parent_visit!=null?$parent_visit_status:null;
            return $parent_visit_status;
        } else {
            //otherwise, return null
            return null;
        }
    }
    /* get the "near home" status of a driver with a parent's home. The function
    returns a DriverZoneVisit record if the driver near parent's home*/
    private function checkDriverLocationParentZone($driver, $parent)
    {
        //if the location of the parent is not set or the alert distance is not set, return null
        if ($parent->address_latitude == null || $parent->address_latitude == null
            || $parent->zone_alert_distance == null) {
            return null;
        }

        // compute the distance between the driver and parent's home
        $dist = $this->getDistance($driver->last_latitude, $driver->last_longitude,
            $parent->address_latitude, $parent->address_longitude);
        // get the last parent zone visit status
        $latest_zone_visit = $driver->latest_zones_visit->firstWhere('parent_id', $parent->id);
        // create a new DriverZoneVisit record
        $parent_zone_visit_status = new DriverZoneVisit;
        $parent_zone_visit_status->driver_id = $driver->id;
        $parent_zone_visit_status->parent_id = $parent->id;
        //check if driver is a zone_alert_distance from parent home
        if ($dist < $parent->zone_alert_distance) {
            //if so, check if we do not send arrived to zone notification
            if ($latest_zone_visit == null ||
                $latest_zone_visit->case_id != $this::$bus_arrived) {
                $parent_zone_visit_status->case_id = $this::$bus_arrived;
            }
        } else {
            //driver is not zone_alert_distance from parent home, check if we do not save left zone status
            if ($latest_zone_visit == null ||
                $latest_zone_visit->case_id != $this::$bus_left) {
                $parent_zone_visit_status->case_id = $this::$bus_left;
            }
        }
        //if there is a ("near home") status
        if ($parent_zone_visit_status->case_id) {
            $parent_zone_visit_status->save();
            //return $latest_zone_visit!=null?$parent_zone_visit_status:null;
            return $parent_zone_visit_status;
        } else {
            //otherwise, return null
            return null;
        }
    }
    /* get the visit status of a driver with the school. The function
    returns a DriverSchoolVisit record if the driver arrived at or left school*/
    private function checkDriverLocationSchool($driver)
    {
        // compute the distance between the driver and school
        $dist = $this->getDistance($driver->last_latitude, $driver->last_longitude,
            $driver->school->latitude, $driver->school->longitude);
        // create a new DriverSchoolVisit record
        $school_visit_status = new DriverSchoolVisit;
        $school_visit_status->driver_id = $driver->id;
        //check if driver near school
        if ($dist < $this::$maxDistance) {
            //if so, check if we do not send arrived to school notification
            if ($driver->latest_school_visit == null ||
                $driver->latest_school_visit->case_id != $this::$bus_arrived) {
                $school_visit_status->case_id = $this::$bus_arrived;
            }
        } else {
            //driver is far from school, check if we do not send left school notification
            if ($driver->latest_school_visit == null ||
                $driver->latest_school_visit->case_id != $this::$bus_left) {
                $school_visit_status->case_id = $this::$bus_left;
            }
        }
        //if there is a visit status ("arrived at" or "left") school
        if ($school_visit_status->case_id) {
            $school_visit_status->save();
            return $school_visit_status;
        } else {
            // otherwise, return null
            return null;
        }
    }

    /* check In/Out child */
    public function checkInOut(Request $request)
    {
        $niceNames = [
            'driver_id' => 'Driver',
            'child_id' => 'Child',
            'secretKey' => 'authentication',
        ];
        $validator = Validator::make($request->all(), [
            'driver_id' => 'required|numeric',
            'child_id' => 'required|numeric',
            'secretKey' => 'required',
            'case_id' => 'required|numeric',
        ], [], $niceNames);
        if ($validator->fails()) {
            //pass validator errors as errors object for ajax response
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $driver =  Driver::findOrFail($request->driver_id);
        // if the secretKey does not match with the driver's one
        if ($driver->secretKey != $request->secretKey) {
            // pass error for ajax response
            return response()->json(['errors' => ['secretKey' => ['secret key does not match']]], 404);
        }
        $child =  Child::findOrFail($request->child_id);

        if($driver && $child){
            // create a new DriverChildCheckInOut record
            $child_check = new DriverChildCheckInOut();
            $child_check->driver_id = $request->driver_id;
            $child_check->child_id = $request->child_id;
            $child_check->case_id = $request->case_id;
            $child_check->save();

            //get parent if this child to sent notification
            $parent =  Parent_::findOrFail($child->parent_id);

            if($request->case_id == 3){
                $message = $child->childName . " just checked in the bus";
            }
            else{
                $message = $child->childName . " just checked out from the bus";
            }

            $messageType = "admin_message";
            // get the current server's time with time zone to be displayed with notification
            $timezone = config('app.timezone');
            $current_datetime = date('Y-m-d H:i:s') . ' ' . $timezone;
            try {
                $this->sendSingleFireBaseNotification($message, $current_datetime, $messageType, $parent->fcm_token);
            } 
            catch(\Kreait\Firebase\Exception\Messaging\NotFound $e) {}
                
            return response()->json(['success' => ['check set successfully']]);
        }
        else{
            return response()->json(['errors' => ['parent not found']], 404);
        }

    }
}
