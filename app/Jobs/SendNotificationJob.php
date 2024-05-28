<?php

namespace App\Jobs;

use App\Repositories\v1\NotificationRepositoryV1;
use App\Repositories\v1\PushNotificationRepositoryV1;
use App\Services\v1\PushNotificationServiceV1;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $data;

    private $notification_type;

    /**
     * Create a new job instance.
     */
    public function __construct($data, $notification_type)
    {
        $this->data = $data;
        $this->notification_type = $notification_type;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $notificationRepositoryV1 = new NotificationRepositoryV1();
        $notificationRepositoryV1
            ->setNotificationType($this->notification_type)
            ->setSenderId($this->data->sender_id)
            ->setReceiverId($this->data->receiver_id)
            ->setDataId($this->data->id)
            ->setTitle($this->notification_type == Config::get('variable_constants.notification_types.friend_request') ?
                Config::get('content.notifications.friend_request') : Config::get('content.notifications.channel_invitation'))
            ->store();

        // Send PushNotification
        $pushNotificationServiceV1 = new PushNotificationServiceV1(new PushNotificationRepositoryV1());
        if ($this->notification_type == Config::get('variable_constants.notification_types.friend_request')) {
            $pushNotificationServiceV1
                ->setType($this->notification_type)
                ->setTitle(Config::get('content.push_notifications.friend_request.title'))
                ->setBody(Config::get('content.push_notifications.friend_request.content'))
                ->setTo($this->data->receiver_id)
                ->setDataId($this->data->id)
                ->sendNotification();
        } elseif ($this->notification_type == Config::get('variable_constants.notification_types.channel_invitation')) {
            $pushNotificationServiceV1
                ->setType($this->notification_type)
                ->setTitle(Config::get('content.push_notifications.channel_invitation.title'))
                ->setBody(Config::get('content.push_notifications.channel_invitation.content'))
                ->setTo($this->data->receiver_id)
                ->setDataId($this->data->id)
                ->sendNotification();
        }
    }
}
