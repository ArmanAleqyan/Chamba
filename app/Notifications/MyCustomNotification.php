<?php

namespace App\Notifications;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FCMNotification;
use Illuminate\Notifications\Notification;

class MyCustomNotification extends Notification
{
    public function toFcm($notifiable)
    {
        return CloudMessage::withTarget('token', $notifiable->device_token)
            ->withNotification(FCMNotification::create()
                ->setTitle('Notification Title')
                ->setBody('Notification body')
            );
    }

    public function via($notifiable)
    {
        return ['fcm']; // Specify that this notification should be sent via Firebase Cloud Messaging (FCM).
    }


}
