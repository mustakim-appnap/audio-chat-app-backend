<?php

namespace App\Services\v1;

use App\Repositories\v1\NotificationRepositoryV1;
use App\Repositories\v1\PushNotificationRepositoryV1;
use Illuminate\Support\Facades\Auth;

class NotificationServiceV1
{
    public function __construct(protected NotificationRepositoryV1 $notificationRepositoryV1, protected PushNotificationRepositoryV1 $pushNotificationRepositoryV1)
    {

    }

    public function getUserNotifications()
    {
        return $this->notificationRepositoryV1->setReceiverId(Auth::id())->setPage(request()->get('page'))->getUserNotifications();
    }

    public function getPushNotificationStatus()
    {
        return $this->pushNotificationRepositoryV1->setUserId(Auth::id())->getPushNotificationStatus();
    }

    public function updatePushNotificationStatus($data)
    {
        $this->pushNotificationRepositoryV1->setUserId(Auth::id())->setIsAllowed($data['is_allowed'])
            ->setChannelInvitationStatus($data['channel_invitation'])
            ->setFriendRequestStatus($data['friend_request'])
            ->setMessageStatus($data['message'])
            ->setPromotionalStatus($data['promotional'])
            ->update();

        return $this->pushNotificationRepositoryV1->getPushNotificationStatus();
    }
}
