<?php

namespace App\Jobs;

use App\Models\FriendRequest;
use App\Models\PrivateChannelInvitation;
use App\Repositories\v1\NotificationRepositoryV1;
use App\Repositories\v1\PushNotificationRepositoryV1;
use App\Services\v1\PushNotificationServiceV1;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;

class UpdateNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $notification_type;

    private $data_id;

    private $response;

    /**
     * Create a new job instance.
     */
    public function __construct($notification_type, $data_id, $response)
    {
        $this->notification_type = $notification_type;
        $this->data_id = $data_id;
        $this->response = $response;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $notificationRepositoryV1 = new NotificationRepositoryV1();
        $notificationRepositoryV1->setNotificationType($this->notification_type)
            ->setDataId($this->data_id)
            ->updateNotificationStatus($this->response);

        // Send Push Notification if request is accepted
        if ($this->notification_type == Config::get('variable_constants.notification_types.friend_request') && $this->response == Config::get('variable_constants.check.yes')) {
            $friendRequest = FriendRequest::where('id', $this->data_id)->select('sender_id', 'receiver_id', 'status')->first();
            $pushNotificationServiceV1 = new PushNotificationServiceV1(new PushNotificationRepositoryV1());
            $pushNotificationServiceV1->setType($this->notification_type)
                ->setTitle(Config::get('content.push_notifications.friend_request_accepted.title'))
                ->setBody(Config::get('content.push_notifications.friend_request_accepted.content'))
                ->setTo($friendRequest->sender_id)
                ->setDataId($this->data_id)
                ->sendNotification();
        } elseif ($this->notification_type == Config::get('variable_constants.notification_types.channel_invitation') && $this->response == Config::get('variable_constants.check.yes')) {
            $invitation = PrivateChannelInvitation::where('id', $this->data_id)->select('sender_id', 'receiver_id')->first();
            $pushNotificationServiceV1 = new PushNotificationServiceV1(new PushNotificationRepositoryV1());
            $pushNotificationServiceV1->setType($this->notification_type)
                ->setTitle(Config::get('content.push_notifications.join_channel.title'))
                ->setBody(Config::get('content.push_notifications.join_channel.content'))
                ->setTo($invitation->sender_id)
                ->setDataId($this->data_id)
                ->sendNotification();
        }
    }
}
