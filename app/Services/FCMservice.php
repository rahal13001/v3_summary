<?php
namespace App\Services;
use Exception;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Laravel\Firebase\Facades\Firebase;

class FCMservice {

    public function sendNotification($token, $title, $body, array $data = [])
    {
        try {
            $messaging = Firebase::messaging();
            $notification = Notification::create($title, $body);
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($notification);
            if (!empty($data)) {
                $message->withData($data);
            }
            
            // Log the data payload for verification
            // Log::info('FCM Data Payload: ', ['data' => $data]);

            $response = $messaging->send($message);
            // Log::info('FCM Response: ', ['response' => $response]);

            return [
                'success' => true,
                'message' => 'Notification Sent Successfully',
                'response' => $response,
            ];
               
        } catch (Exception $e) {
            Log::error('FCM Error: ', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Failed To Set Notification'.$e->getMessage()
            ];
        }
    }

}