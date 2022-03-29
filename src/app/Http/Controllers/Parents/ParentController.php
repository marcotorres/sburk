<?php

namespace App\Http\Controllers\Parents;


use App\Child;
use App\DriverChildCheckInOut;
use App\Http\Controllers\SmsController;
use App\Parent_;
use App\User;
use App\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Setting;
use App\SettingType;
use Validator;
use DB;
use Illuminate\Support\Facades\Storage;
use App\DriverSchoolVisit;
use App\DriverParentVisit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ParentController extends SmsController
{

    /* get the map view*/
    public function showmap()
    {
        $setting = Setting::where('name','Google maps API key')->first();
        return view('parents.map', ['GOOGLE_MAPS_API_KEY' => $setting->value]);
    }

    /* ***************************** Web APIs ********************************/

    /* get all parents based on filter */
    public function filter(Request $request)
    {
        // get the current logged school
        $school = Auth::user();
        // get all parents with names that match the filter term
        $query = Parent_::query();
        $query->withCount(['children']);
        $query->leftJoin('drivers','driver_id',"=","drivers.id");
        if ($request->search) {
            $query->where('parents.name', 'LIKE', '%' . $request->search . '%')
            ->orWhere('drivers.name', 'LIKE', '%' . $request->search . '%')
            ->orWhere('drivers.tel_number', 'LIKE', '%' . $request->search . '%');
        }
        $query->where('parents.school_id', $school->id);
        // order the obtained parents with the requred order column
        $parents = $query->orderBy($request->input('orderBy.column'), $request->input('orderBy.direction'))
            ->paginate($request->input('pagination.per_page'));
        //load buses data with the obtained parents    
        $parents->load('driver');
        //return the obtained parents
        return $parents;
    }
    /* get all parents in school account */
    public function all()
    {
        // get the current logged school
        $school = Auth::user();
        // get all parents within the school account
        $query = Parent_::query();
        return $query->where('school_id', $school->id)->get();
    }

    /* get all parents in school account */
    public function upload(Request $request)
    {
        // get the current logged school
        $school = Auth::user();

        $file = $request->file('school_parents_file');
        if($file==null || $file->extension()!="xlsx" || !$file->isValid())
            return response()->json(['errors' => ['Bad file']], 500);

        $file_path = Storage::put('files/'.$school->id.'/', $file);

        if ( $parents_data = SimpleXLSX::parse(Storage::path($file_path))) {
            $parents_data = $parents_data->rows();
            for ($i=1; $i < sizeof($parents_data); $i++) {
                $child_names = [];
                $name = $parents_data[$i][0];
                $country_code = $parents_data[$i][1];
                $tel_number = $parents_data[$i][2];
                if (sizeof($parents_data[$i]) > 3)
                {
                    $child_count = sizeof($parents_data[$i]);
                    for ($j=3; $j < $child_count; $j++)
                    {
                        if($parents_data[$i][$j] != "")
                        {
                            array_push($child_names, $parents_data[$i][$j]);
                        }
                    }
                }
                $rules['name'] = 'required|string';
                $rules['country_code'] = 'required|numeric';
                $rules['tel_number'] = 'required|numeric';

                $validator = Validator::make(['name' => $name, 'country_code' => $country_code, 'tel_number' => $tel_number], $rules);

                //Now check validation:
                if ($validator->fails())
                {
                    return response()->json(['errors' => ['Incorrect values in the uploaded file']], 500);
                }

                DB::beginTransaction();
                try {
                    //create the parent
                    $parent = Parent_::create([
                        'name' => $name,
                        'tel_number' => $tel_number,
                        'country_code' => $country_code,
                        'secretKey' => uniqid(),
                        'school_id' => $school->id,
                    ]);

                    $parent_id =  $parent->id;

                    foreach ($child_names as $child_name){
                        //dd($child_name);
                        $child = new Child;
                        $child->childName = $child_name;
                        $child->parent_id = $parent_id;
        
                        $child->save();
                    }
                    //save
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollback();
                    return response()->json(['errors' => ['Error'=> [$e->getMessage()]]], 422);
                }
            }
            return response()->json(['success' => ['parents updated successfully']]);
        } else {
            return response()->json(['errors' => ['Incorrect template file']], 500);
        }


        // get all parents within the school account
        //$query = Parent_::query();
        //return $query->where('school_id', $school->id)->get();
    }

    /* Mass assign drivers to some parents in school account */
    public function assignDrivers(Request $request)
    {
        // get the current logged school
        $school = Auth::user();

        //get data from request
        $all = $request->all();
        if(!array_key_exists("params", $all))
            return response()->json(['errors' => ['Bad request']], 500);

        $all = $all["params"];

        if(!array_key_exists("selecteddriver", $all) || !array_key_exists("selectedparents", $all))
            return response()->json(['errors' => ['Bad request']], 500);

        $selected_driver = $all["selecteddriver"];
        $selected_parents = $all["selectedparents"];

        if($selected_driver==null)
            return response()->json(['errors' => ['No driver selected']], 500);

        //check if the driver is in the school account
        if($selected_driver["school"]["id"]!=$school->id)
        {
            return response()->json(['errors' => ['Driver is not belonging to school']], 500);
        }

        DB::beginTransaction();
        try {
            $parentTable = (new Parent_())->getTable();
            DB::table($parentTable)
                ->whereIn('id', $selected_parents)
                ->update(array('driver_id' => $selected_driver["id"]));

            //save
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['errors' => ['Error'=> [$e->getMessage()]]], 422);
        }

        return response()->json(['success' => ['parents updated successfully']]);
    }

    /* get a specific parent */
    public function show($parent)
    {
        return Parent_::findOrFail($parent->id);
    }

    /* create a new parent */
    public function store(Request $request)
    {
        $niceNames = array(
            'parent.name' => 'parent name',
            'parent.country_code' => 'Country code',
            'parent.driver' => 'Driver',
            'parent.tel_number' => 'telephone number',
            'rowData.*.cname' => 'Child name',
        );
        // validate the request
        $validator = Validator::make($request->all()['params'], [
            'rowData' => 'present|array',
            'rowData.*.cname' => 'required|string',

            'parent.name' => 'required|string',
            'parent.driver' => 'required',
            'parent.country_code' => 'required|numeric',
            'parent.tel_number' => 'required|numeric|unique:parents,tel_number',

        ], []);
        $validator->setAttributeNames($niceNames);

        if ($validator->fails()) {
            //pass validator errors as errors object for ajax response
            return response()->json(['errors'=>$validator->errors()], 422);
        }

        $all = $request->all()['params'];

        $parentData = $all["parent"];
        $childrenData = $all["rowData"];

        // get the current logged school
        $school = Auth::user();

        $parent = Parent_::create([
            'name' => $parentData['name'],
            'tel_number' => $parentData['tel_number'],
            'country_code' => $parentData['country_code'],
            'secretKey' => uniqid(),
            'school_id' => $school->id,
            'driver_id' => $parentData['driver']['id'],
        ]);

        $parent_id =  $parent->id;

        DB::beginTransaction();
        try {
            foreach ($childrenData as $childData){
                $child = new Child;
                $child->childName = $childData['cname'];
                $child->parent_id = $parent_id;

                $child->save();
                DB::commit();
            }
        }
        finally{
            DB::rollBack();
        }
    }

    /* get children of this parent */
    public function getChildren($parent){
        $school = Auth::user();

        $query = Child::query();
        $children = $query->where('parent_id', $parent)->get();

        $data = [];
        if ($children){
            foreach ($children as $child) {
                array_push($data, Child::with(['driver'])
                    ->findOrFail($child->id));
            }
        }
        return response()->json(['children' => $data]);
    }

    /* update a specific parent */
    public function update(Request $request)
    {
        $all = $request->all()['params'];

        $parentData = $all["parent"];
        $childrenData = $all["rowData"];

        // get the required parent to be updated
        $parent = Parent_::find($parentData['id']);
        //if the parent found
        if ($parent) {
            // make nice names for validation errors
            $niceNames = array(
                'parent.name' => 'parent name',
                'parent.country_code' => 'Country code',
                'parent.driver' => 'Driver',
                'parent.tel_number' => 'telephone number',
                'rowData.*.childName' => 'Child name',
            );
            // validate the request
            $validator = Validator::make($request->all()['params'], [
                'rowData' => 'present|array',
                'rowData.*.childName' => 'required|string',

                'parent.name' => 'required|string',
                'parent.driver' => 'required',
                'parent.country_code' => 'required|numeric',
                'parent.tel_number' => ['required', 'numeric', Rule::unique('parents', 'tel_number')->ignore($parent->id)],
            ], []);
            $validator->setAttributeNames($niceNames);

            if ($validator->fails()) {
                //pass validator errors as errors object for ajax response
                return response()->json(['errors'=>$validator->errors()], 422);
            }

            // get the current logged school
            $school = Auth::user();
            if($parentData['school_id'] != $school->id)
                return response()->json(['errors' => ['Parent'=> ['parent is not belonging to this school']]], 403);

            //update the parent
            $parent->name = $parentData['name'];
            $parent->tel_number = $parentData['tel_number'];
            $parent->country_code = $parentData['country_code'];
            $parent->school_id = $school->id;
            $parent->driver_id = $parentData['driver']['id'];
            $parent->verified = $parentData['verified'];
            $parent->save();
            DB::commit();

            $parent_id =  $parent->id;

            Child::where('parent_id', $parent_id)->delete();

            DB::beginTransaction();

            if($childrenData){
                try {
                    foreach ($childrenData as $childData){
                        $child = new Child;
                        $child->childName = $childData['childName'];
                        $child->parent_id = $parent_id;

                        $child->save();
                        DB::commit();
                    }
                }
                finally{
                    DB::rollBack();
                }
            }
        }
    }

    /* delete a specific parent */
    public function destroy($parent)
    {
        $parent->delete();
    }

    /* delete multiple parents */
    public function deleteMany(Request $request)
    {

        // get the current logged school
        $school = Auth::user();

        //get data from request
        $all = $request->all();

        if(!array_key_exists("selectedparents", $all))
            return response()->json(['errors' => ['Bad request']], 500);

        $selected_parents = $all["selectedparents"];

        DB::beginTransaction();
        try {
            $parentTable = (new Parent_())->getTable();
            //get parents from DB to make sure they are belong to school
            $parentsFromDb = DB::table($parentTable)
                ->whereIn('id', $selected_parents)
                ->where('school_id', $school->id)->get()->pluck('id');

            //delete them
            DB::table($parentTable)
                ->whereIn('id', $parentsFromDb)
                ->delete();

            //save
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['errors' => ['Error'=> [$e->getMessage()]]], 422);
        }

        return response()->json(['success' => ['parents deleted successfully']]);
    }

    /* get a specific parent with asscoiated driver*/
    public function getParent($parent)
    {
        return Parent_::with('driver')->findOrFail($parent->id);
    }

    /* ***************************** Mobile APIs ********************************/

    /* get a parent information using his telephone number */
    public function getParentTelNumber(Request $request)
    {
        $niceNames = [
            'tel_number' => 'telephone number',
        ];
        $validator = Validator::make($request->all(), [
            'secretKey' => 'required',
            'country_code' => 'required',
            'tel_number' => 'required|numeric'], [], $niceNames);

        if ($validator->fails()) {
            //pass validator errors as errors object for ajax response
            return response()->json(['errors'=>$validator->errors()], 422);
        }
        // get the parent based on request data
        $query = Parent_::query();
        $query->where('tel_number', $request->tel_number);
        $query->where('country_code', $request->country_code);
        $parent = $query->where('secretKey', $request->secretKey)->first();
        if($parent)
            return response()->json(['parent' => Parent_::with(['driver', 'school', 'children'])
                ->findOrFail($parent->id)]);
        else
            return response()->json(['errors' => ['id'=> ['parent not exists']]], 404);


    }
    /* get a parent information with school and driver's bus data using his telephone number */
    public function getSchoolBusDriverParentTelNumber(Request $request)
    {
        $niceNames = [
            'tel_number' => 'telephone number',
        ];
        $validator = Validator::make($request->all(), [
            'secretKey' => 'required',
            'country_code' => 'required',
            'tel_number' => 'required|numeric'], [], $niceNames);

        if ($validator->fails()) {
            //pass validator errors as errors object for ajax response
            return response()->json(['errors'=>$validator->errors()], 422);
        }
        // get the parent based on request data
        $query = Parent_::query();
        $query->where('tel_number', $request->tel_number);
        $query->where('country_code', $request->country_code);
        $parent = $query->where('secretKey', $request->secretKey)->first();
        if($parent)
            return response()->json(['parent' => Parent_::with(['driver.bus', 'school'])
                ->findOrFail($parent->id)]);
        else
            return response()->json(['errors' => ['id'=> ['parent not exists']]], 404);

    }
    /* update the current location of a parent */
    public function updatePosition(Request $request)
    {
        // try to find the parent based on his id
        $parent = Parent_::find($request->id);
        //if found
        if($parent)
        {
            // make nice names for validation
            $niceNames = [
                'address_latitude' => 'latitude',
                'address_longitude' => 'longitude',
                'secretKey' => 'authentication',
            ];
            // validate the request
            $validator = Validator::make($request->all(), [
                'secretKey' => 'required',
                'address_latitude' => 'required|numeric',
                'address_longitude' => 'required|numeric',
            ], [], $niceNames);
            // if validation errors
            if ($validator->fails()) {
                //pass validator errors as errors object for ajax response
                return response()->json(['errors'=>$validator->errors()], 422);
            }
            // if the secretKey does not match with the parent's one
            if($parent->secretKey != $request->secretKey)
                return response()->json(['errors' => ['secretKey'=> ['secret key does not match']]], 422);
            // update the location of the parent
            $parent->address_latitude = $request->address_latitude;
            $parent->address_longitude = $request->address_longitude;
            $parent->save();
            //return a success message for ajax request
            return response()->json(['success' => ['parent location updated successfully']]);
        }
        else
            return response()->json(['errors' => ['id'=> ['parent not exists']]], 422);
    }

    /* update the child absent time of a parent */
    public function updateChildAbsent(Request $request)
    {
        $niceNames = [
            'id' => 'child',
        ];
        $validator = Validator::make($request->all(), [
                'id' => 'required|numeric']
            , [], $niceNames);
        if ($validator->fails()) {
            //pass validator errors as errors object for ajax response
            return response()->json(['errors'=>$validator->errors()], 422);
        }
        // try to find the child based on his id
        $child = Child::find($request->id);
        //if found
        if($child)
        {
            // update the absent date of the parent
            $child->child_absent_till = $request->tomorrow_date;
            $child->save();
            //return a success message for ajax request
            return response()->json(['child' => $child]);
        }
        else
            return response()->json(['errors' => ['id'=> ['child not exists']]], 422);
    }

    /* authenticate a parent using his telephone number. This function is called 
    when the parent sign ups for the first time on mobile device */
    public function authParentTelNumber(Request $request)
    {
        $niceNames = [
            'tel_number' => 'telephone number',
        ];
        $validator = Validator::make($request->all(), [
                'country_code' => 'required',
                'tel_number' => 'required|numeric']
            , [], $niceNames);
        if ($validator->fails()) {
            //pass validator errors as errors object for ajax response
            return response()->json(['errors'=>$validator->errors()], 422);
        }
        // get the parent based on his telephone number
        $query = Parent_::query();
        $parent = $query->where('tel_number', $request->tel_number)
            ->where('country_code', $request->country_code)->first();
        //if parent exists
        if($parent)
        {
            // generate random verificaion code and save it to the parent record
            $pin = mt_rand(100000, 999999);
            $parent->v_code= $pin;
            // clear the verified field in the parent record
            $parent->verified= 0;
            //check if the super admin enabled sms verification
            $is_sms_enabled = SettingType::where('name','SMS')->first()->enabled;
            if($is_sms_enabled)
            {
                $system_name = Setting::where('name', 'System name')->first()->value;
                $messageBody = $parent->v_code ." is your verification code from ". $system_name;
                $recieverTelNumber = "+". $parent->country_code. $parent->tel_number;
                Log::info("Sending SMS to ". $recieverTelNumber);
                $this->sendSms($messageBody, $recieverTelNumber);
                // send success
            }
            $parent->save();
            return response()->json(['success' => ['verification code sent successfully']]);
        }
        else
            return response()->json(['errors' => ['parent not exists']], 404);
    }
    /* verifies a parent using his tel number on mobile device */
    public function verifyParentTelNumber(Request $request)
    {
        $niceNames = [
            'tel_number' => 'telephone number',
            'v_code' => 'verification code',
        ];
        $validator = Validator::make($request->all(), [
            'country_code' => 'required',
            'fcm_token' => 'required',
            'tel_number' => 'required|numeric',
            'v_code' => 'required|numeric',
        ], [], $niceNames);
        if ($validator->fails()) {
            //pass validator errors as errors object for ajax response
            return response()->json(['errors'=>$validator->errors()], 422);
        }
        // get the parent using his telephone number and verification code
        $query = Parent_::query();
        $parent = $query->
        where('tel_number', $request->tel_number)->
        where('country_code', $request->country_code)->
        where('v_code', $request->v_code)->first();

        //if his telephone number and verification code matched
        if($parent)
        {
            // set the verified field in the parent record
            $parent->verified= 1;
            // set the FCM token field in the parent record
            $parent->fcm_token= $request->fcm_token;
            // send success
            $parent->save();
            return response()->json(['parent' => Parent_::with(['driver.bus', 'school'])
                ->findOrFail($parent->id)]);
        }
        else
            return response()->json(['errors' => ['verification code is not correct']], 404);
    }
    /* set the alert distance of the parent. If the driver is with distance to the parent's home, the 
    parent will receive a notification */
    public function setZoneAlertDistance(Request $request)
    {
        //find the parent by the id in the request
        $parent = Parent_::find($request->id);
        if($parent)
        {
            $niceNames = [
                'secretKey' => 'authentication',
            ];
            $validator = Validator::make($request->all(), [
                'secretKey' => 'required',
                'zoneAlertDistance' => 'required|numeric',
            ], [], $niceNames);
            if ($validator->fails()) {
                //pass validator errors as errors object for ajax response
                return response()->json(['errors'=>$validator->errors()], 422);
            }
            //if the secrete key of the parent does not match with the stored one, return error response
            if($parent->secretKey != $request->secretKey)
                return response()->json(['errors' => ['secretKey'=> ['secret key does not match']]], 404);

            // set the distance in the parent record and save
            $parent->zone_alert_distance = $request->zoneAlertDistance==0? null:$request->zoneAlertDistance;
            $parent->save();
            return response()->json(['success' => ['parent zone alert distance updated successfully']]);
        }
        else
            return response()->json(['errors' => ['id'=> ['parent not exists']]], 404);
    }
    /* set a Setting from the iOS app */
    public function setSetting(Request $request)
    {
        //find the parent by the id in the request
        $parent = Parent_::find($request->id);
        if($parent)
        {
            $niceNames = [
                'secretKey' => 'authentication',
            ];
            $validator = Validator::make($request->all(), [
                'secretKey' => 'required',
                'setting' => 'required|numeric',
                'value' => 'required|numeric',
            ], [], $niceNames);
            if ($validator->fails()) {
                //pass validator errors as errors object for ajax response
                return response()->json(['errors'=>$validator->errors()], 422);
            }
            //if the secrete key of the parent does not match with the stored one, return error response
            if($parent->secretKey != $request->secretKey)
                return response()->json(['errors' => ['secretKey'=> ['secret key does not match']]], 404);

            $settingName = "";
            switch ($request->setting) {
                case 1: //arrived_school
                    $parent->arrived_school = $request->value;
                    break;
                case 2: //left_school
                    $parent->left_school = $request->value;
                    break;
                case 3: //arrived_home
                    $parent->arrived_home = $request->value;
                    break;
                case 4: //left_home
                    $parent->left_home = $request->value;
                    break;
                default:
                    break;
            }
            $parent->save();
            return response()->json(['success' => ['parent setting updated successfully']]);
        }
        else
            return response()->json(['errors' => ['id'=> ['parent not exists']]], 404);
    }
    /* get the event log of specific driver */
    public function getDriverLog(Request $request)
    {
        // try to find the parent based on his id
        $parent = Parent_::with(['driver'])->find($request->id);
        //if found
        if($parent)
        {
            // make nice names for validation
            $niceNames = [
                'secretKey' => 'authentication',
            ];
            // validate the request
            $validator = Validator::make($request->all(), [
                'secretKey' => 'required',
                'page' => 'required|numeric',
            ], [], $niceNames);
            // if validation errors
            if ($validator->fails()) {
                // pass validator errors as errors object for ajax response
                return response()->json(['errors'=>$validator->errors()], 422);
            }
            // if the secretKey does not match with the parent's one
            if($parent->secretKey != $request->secretKey)
            {
                // pass error for ajax response
                return response()->json(['errors' => ['secretKey'=> ['secret key does not match']]], 404);
            }

            $per_page = 7;
            $page = $request->page;

            $latest_driver_school_visits = DriverSchoolVisit::select(DB::raw("case_id, updated_at, '1' AS event_place"))
                ->where('driver_id', $parent->driver_id)
                ->latest('updated_at')->first();

            $latest_driver_parent_visits = DriverParentVisit::select(DB::raw("case_id, updated_at, '2' AS event_place"))
                ->where('driver_id', $parent->driver_id)
                ->where('parent_id', $parent->id)
                ->latest('updated_at')->first();

            if($latest_driver_parent_visits == null && $latest_driver_school_visits!=null)
            {
                $recent_date = $latest_driver_school_visits->updated_at;
            }
            else if($latest_driver_parent_visits != null && $latest_driver_school_visits==null)
            {
                $recent_date = $latest_driver_parent_visits->updated_at;
            }
            else if($latest_driver_parent_visits != null && $latest_driver_school_visits != null)
            {
                $recent_date = max([$latest_driver_school_visits->updated_at, $latest_driver_parent_visits->updated_at]);
            }
            else
            {
                return response()->json(['eventLog' => []], 200);
            }
            
            if($recent_date)
            {
                $to_date = Carbon::parse($recent_date)->addDay(1)->subDays($per_page*($page-1))->toDateString();

                $from_date = Carbon::parse($recent_date)->subDays($per_page*($page))->toDateString();

                $driver_school_visits = DriverSchoolVisit::select(DB::raw("case_id, updated_at, '1' AS event_place"))
                    ->where('updated_at', '>=', $from_date)
                    ->where('updated_at', '<', $to_date)
                    ->where('driver_id', $parent->driver_id)->get();

                $driver_parent_visits = DriverParentVisit::select(DB::raw("case_id, updated_at, '2' AS event_place"))
                    ->where('updated_at', '>=', $from_date)
                    ->where('updated_at', '<', $to_date)
                    ->where('driver_id', $parent->driver_id)
                    ->where('parent_id', $parent->id)->get();

                $all = $driver_school_visits->concat($driver_parent_visits);
                $all = $all->sortByDesc('updated_at')->values()->all();

                return response()->json(['eventLog' => $all], 200);
            }
            else
            {
                return response()->json(['eventLog' => []], 200);
            }
        }
        else
            return response()->json(['errors' => ['id'=> ['parent not exists']]], 404);
    }


    /* get the event log of specific driver */
    public function getChildLog(Request $request)
    {
        // try to find the parent based on his id
        $parent = Parent_::with(['driver', 'children'])->find($request->id);
        //if found
        if($parent)
        {
            // make nice names for validation
            $niceNames = [
                'secretKey' => 'authentication',
            ];
            // validate the request
            $validator = Validator::make($request->all(), [
                'secretKey' => 'required',
                'page' => 'required|numeric',
            ], [], $niceNames);
            // if validation errors
            if ($validator->fails()) {
                // pass validator errors as errors object for ajax response
                return response()->json(['errors'=>$validator->errors()], 422);
            }
            // if the secretKey does not match with the parent's one
            if($parent->secretKey != $request->secretKey)
            {
                // pass error for ajax response
                return response()->json(['errors' => ['secretKey'=> ['secret key does not match']]], 404);
            }

            $childrenIds = $parent->children->pluck('id');
            $per_page = 7;
            $page = $request->page;

            $latest_driver_child_checks = DriverChildCheckInOut::select(DB::raw("case_id, updated_at, '3' AS event_place"))
                ->where('driver_id', $parent->driver_id)
                ->whereIn('child_id', $childrenIds)
                ->latest('updated_at')->first();

            if($latest_driver_child_checks)
            {
                $recent_date = $latest_driver_child_checks->updated_at;

                $to_date = Carbon::parse($recent_date)->addDay(1)->subDays($per_page*($page-1))->toDateString();
    
                $from_date = Carbon::parse($recent_date)->subDays($per_page*($page))->toDateString();
    
                $driver_child_checks_in_out = DriverChildCheckInOut::with('child')->select(DB::raw("child_id, case_id, updated_at"))
                    ->where('updated_at', '>=', $from_date)
                    ->where('updated_at', '<', $to_date)
                    ->where('driver_id', $parent->driver_id)
                    ->whereIn('child_id', $childrenIds)->get();
    
    
                $all = $driver_child_checks_in_out->sortByDesc('updated_at')->values()->all();
    
                return response()->json(['eventLog' => $all], 200);
            }
            else
            {
                return response()->json(['eventLog' => []], 200);
            }

        }
        else
            return response()->json(['errors' => ['id'=> ['parent not exists']]], 404);
    }
}
