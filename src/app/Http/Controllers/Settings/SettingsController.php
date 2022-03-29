<?php

namespace App\Http\Controllers\Settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Setting;
use App\SettingType;
use DB;
use Validator;
class SettingsController extends Controller
{
    /* get all settings */
    public function getSettings()
    {
        return SettingType::with(['settings'])->get();
    }
    /* update the values of the settings */
    public function updateSettings (Request $request)
    {
        $setting_types = $request->all();
        DB::beginTransaction();
        try
        {
            // update the corresponding value for each setting
            foreach ($setting_types as $setting_type) {
                $sms_gateway = 'none';
                if($setting_type['name'] === "SMS")
                {
                    $enabled_value = 0;
                    foreach ($setting_type["settings"] as $setting) {
                        if($setting["name"] === 'SMS Gateway')
                        {
                            if($setting["value"] !== "none")
                            {
                                $enabled_value = 1;
                                $sms_gateway = $setting["value"];
                            }
                            break;
                        }
                    }
                    $setting_type["enabled"] = $enabled_value;
                }
                DB::table('setting_types')
                ->where('id', '=', $setting_type["id"])
                ->update([
                    'enabled' =>  $setting_type["enabled"],
                    'updated_at' => now(),
                ]);

                if($setting_type['name'] === "SMS")
                {
                    foreach ($setting_type["settings"] as $setting) {
                        if($setting["name"] === 'SMS Gateway')
                        {
                            DB::table('settings')
                            ->where('id', '=', $setting["id"])
                            ->update([
                                'value' => $setting["value"],
                                'updated_at' => now(),
                            ]);
                        }
                        else
                        {
                            // Test if setting name contains the sms_gateway 
                            if(strpos(strtolower($setting['name']), $sms_gateway) !== false){
                                
                                $validator = Validator::make($setting, [
                                    'value' => 'required']);
    
                                    if ($validator->fails()) {
                                        //pass validator errors as errors object for ajax response
                                        return response()->json(['errors' => $setting["name"] . ' is required'], 422);
                                    }
    
                                    DB::table('settings')
                                    ->where('id', '=', $setting["id"])
                                    ->update([
                                        'value' => $setting["value"],
                                        'updated_at' => now(),
                                    ]);
                            }
                        }

                    }
                }
                else
                {
                    if($setting_type["enabled"] === null || $setting_type["enabled"] === 1)
                    {
                        foreach ($setting_type["settings"] as $setting) {
                            $validator = Validator::make($setting, [
                            'value' => 'required']);
                
                            if ($validator->fails()) {
                                //pass validator errors as errors object for ajax response
                                return response()->json(['errors' => $setting["name"] . ' is required'], 422);
                            }
    
                            DB::table('settings')
                            ->where('id', '=', $setting["id"])
                            ->update([
                                'value' => $setting["value"],
                                'updated_at' => now(),
                            ]);
                        }
                    }

                }
            }
            // when done commit
            DB::commit();
            return response()->json(['success' => ['settings updated successfully']]);
        }
        catch (\Exception $e)
        {
            dd($e);
            // rollback if errors
            DB::rollback();
            return response()->json(['errors' => ['settings not updated']], 500);
        }
    }
}
