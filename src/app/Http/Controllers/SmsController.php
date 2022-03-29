<?php

namespace App\Http\Controllers;

use Twilio\Rest\Client;
use Illuminate\Http\Request;
use App\Setting;
use App\SettingType;

class SmsController extends Controller
{
    /* send sms via twilio acount */
    public function sendSms($messageBody, $recieverTelNumber)
    {
        // get which SMS gateway enabled
        $sms_gateway = Setting::where('name', 'SMS Gateway')->first()->value; //twilio, textlocal, branded sms, infobip
        if($sms_gateway === "twilio")
        {
            $sid = Setting::where('name', 'Twilio account SID')->first()->value;
            $token = Setting::where('name', 'Twilio auth token')->first()->value;
            $from = Setting::where('name', 'Twilio phone number')->first()->value;
    
            try
            {
                $twilio = new Client($sid, $token);
    
                $message = $twilio->messages
                                ->create($recieverTelNumber, // to
                                        array(
                                            "body" => $messageBody,
                                            "from" => $from,
                                        )
                                );
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
            }
        }
        else if($sms_gateway === "textlocal")
        {
            $apiKey = Setting::where('name', 'Textlocal Apikey')->first()->value;
            $from = Setting::where('name', 'Textlocal Sender')->first()->value;

            // Account details
            $apiKey = urlencode($apiKey);


            // Message details
            $numbers = array($recieverTelNumber);
            //From name
            $sender = urlencode($from);
            $message = rawurlencode($messageBody);

            $numbers = implode(',', $numbers);

            // Prepare data for POST request
            $data = 'apikey=' . $apiKey . '&numbers=' . $numbers . "&sender=" . $sender . "&message=" . $message;
        
            // Send the GET request with cURL
            $ch = curl_init('https://api.textlocal.in/send/?' . $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
            
            // Process your response here
            //echo $response;
        }
        else if($sms_gateway === "branded sms")
        {
            $apikey = Setting::where('name', 'Branded SMS Apikey')->first()->value;
            $sender = Setting::where('name', 'Branded SMS Sender')->first()->value;

            $number =   $recieverTelNumber;               //  Mobile Number
            $mask   =   $sender;                          //  Registered Mask Name
            $text   =   $messageBody;                     //  Message Content
            $url = 'https://www.branded.smstoconnect.com/api/sendsms.php?apikey='.$apikey.'&phone='.$number.'&message='.urlencode($text).'&sender='.urlencode($mask);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            $result = curl_exec ($ch);
            //echo $result;
        }
        else if($sms_gateway === "infobip")
        {
            $apikey = Setting::where('name', 'Infobip Apikey')->first()->value;
            $postUrl = Setting::where('name', 'Infobip BaseUrl')->first()->value;


            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $postUrl."/sms/2/text/advanced",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS =>"{\"messages\":[{\"destinations\":[{\"to\":\"$recieverTelNumber\"}],\"text\":\"$messageBody\"}]}",
                CURLOPT_HTTPHEADER => array(
                    "Authorization: App ".$apikey,
                    "Content-Type: application/json",
                    "Accept: application/json"
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            //echo $response;
        }
    }
}
