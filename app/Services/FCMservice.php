<?php
namespace App\Services;
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
           $response = $messaging->send($message);

            return [
                'success' => true,
                'message' => 'Notification Sent Successfully',
                'response' => $response,
            ];
               
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed To Set Notification'.$e->getMessage()
            ];
        }
    }

}