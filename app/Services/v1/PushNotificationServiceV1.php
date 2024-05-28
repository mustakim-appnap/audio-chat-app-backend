<?php

namespace App\Services\v1;

use App\Repositories\v1\PushNotificationRepositoryV1;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Pusher\PushNotifications\PushNotifications;

class PushNotificationServiceV1
{
    private $title;

    private $body;

    private $data_id;

    private $type;

    private $from_user;

    private $to_user;

    private $instance_id;

    private $secret_key;

    private $user_id_prefix;

    private $beams_client;

    private $mutable_content;

    private $priority;

    private $badge;

    public function __construct(protected PushNotificationRepositoryV1 $pushNotificationRepositoryV1)
    {
        $this->instance_id = env('PUSHER_INSTANCE_ID');
        $this->secret_key = env('PUSHER_SECRET_KEY');
        $this->user_id_prefix = env('PUSHER_USER_PREFIX');
        $this->badge = 1;
        $this->mutable_content = 1;
        $this->priority = 10;
        $this->initPusher();
    }

    public function initPusher()
    {
        $this->beams_client = new PushNotifications([
            'instanceId' => $this->instance_id,
            'secretKey' => $this->secret_key,
        ]);
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    public function setDataId($data_id)
    {
        $this->data_id = $data_id;

        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function setFrom($from_user)
    {
        $this->from_user = $from_user;

        return $this;
    }

    public function setTo($to_user)
    {
        $this->to_user = $to_user;

        return $this;
    }

    public function sendNotification()
    {
        $push_notification = $this->pushNotificationRepositoryV1->setUserId($this->to_user)->getPushNotificationStatus();
        if ($push_notification->is_enabled) {
            $friend_request_notification_enabled = $this->type == Config::get('variable_constants.notification_types.friend_request') && $push_notification->friend_request == Config::get('variable_constants.activation.active');
            $channel_invitation_notification_enabled = $this->type == Config::get('variable_constants.notification_types.channel_invitation') && $push_notification->channel_invitation == Config::get('variable_constants.activation.active');
            $message_notification_enabled = $this->type == Config::get('variable_constants.notification_types.message') && $push_notification->message == Config::get('variable_constants.activation.active');
            $promotional_notification_enabled = $this->type == Config::get('variable_constants.notification_types.promotional') && $push_notification->promotional == Config::get('variable_constants.activation.active');

            if ($friend_request_notification_enabled || $channel_invitation_notification_enabled || $message_notification_enabled || $promotional_notification_enabled) {
                return $this->preparePushNotificationData();
            } else {
                return null;
            }
        } else {
            return 'user push notification is disabled';
        }

    }

    public function preparePushNotificationData()
    {
        if (! (Auth::id() == $this->to_user)) {
            $notification_data = [
                'data-id' => $this->data_id,
                'data-type' => $this->type,
            ];
            $aps = [
                'alert' => [
                    'title' => $this->title,
                    'body' => $this->body,
                ],
                'badge' => $this->badge,
                'mutable-content' => $this->mutable_content,
                'priority' => $this->priority,
                'data' => $notification_data,
                'sound' => 'default',
            ];

            $published_response = $this->beams_client->publishToUsers([
                $this->user_id_prefix.$this->to_user], [
                    'apns' => [
                        'aps' => $aps,
                    ],
                ]);

            return $published_response;
        }
    }
}
