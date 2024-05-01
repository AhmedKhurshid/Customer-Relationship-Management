<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PushNotificationController extends Controller
{
    // public function pushNotification()
	//     {

	//         $data=[];
	//         $data['message']= "Some message";

	//         $data['booking_id']="my booking booking_id";

    //         $tokens = [];
    //         $tokens[] = 'YOUR_TOKEN';
	//         $response = $this->sendFirebasePush($tokens,$data);

	//     }

        public function sendFirebasePush($tokens, $data)
	    {

	        // $serverKey = 'AAAA0jgBAHE:APA91bErRgT43gC7RFDqQNDjkrVu6ak3JvqswIMcjAi7yqxkCO8C_xIBQFeJq6KUwPOxn-Wcc648JP170Lc-OgzNcmmQ5d3Dli__XLYQK0JB7UvaieQPCWPr84GxM6yeRstwfrVf6x1V';

            $serverKey = env('FCM_SERVER_KEY');

	        // prep the bundle
	        // $msg = array
	        // (
	        //     'message'   => $data['message'],
	        //     'booking_id' => $data['booking_id'],
	        // );

	        $notifyData = [
                 "body" => $data['message'],
                 "title" => $data['title'],
                //  "title"=> "Port App"
            ];

	        $registrationIds = $tokens;

	        if(count($tokens) > 1){
                $fields = array
                (
                    'registration_ids' => $registrationIds, //  for  multiple users
                    'notification'  => $notifyData,
                    'data'=> null,
                    // 'data'=> $msg,
                    // 'priority'=> 'high'
                );
            }
            else{

                $fields = array
                (
                    'to' => $registrationIds[0], //  for  only one users
                    'notification'  => $notifyData,
                    'data'=> null,
                    // 'data'=> $msg,
                    // 'priority'=> 'high'
                );
            }

	        $headers[] = 'Content-Type: application/json';
	        $headers[] = 'Authorization: key='. $serverKey;


	        $ch = curl_init();

	        curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
	        curl_setopt( $ch,CURLOPT_POST, true );
	        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
	        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
	        // curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
	        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
	        $result = curl_exec($ch );
	        if ($result === FALSE)
	        {
	            die('FCM Send Error: ' . curl_error($ch));
	        }
	        curl_close( $ch );
	        return $result;

	    }
}
