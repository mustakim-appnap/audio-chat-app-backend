<?php

namespace App\Services\v1;

use App\Jobs\DeleteNotificationJob;
use App\Jobs\SendNotificationJob;
use App\Jobs\UpdateNotificationJob;
use App\Repositories\v1\FriendRequestRepositoryV1;
use App\Repositories\v1\NotificationRepositoryV1;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class FriendRequestServiceV1
{
    public function __construct(protected FriendRequestRepositoryV1 $friendRequestRepositoryV1, protected NotificationRepositoryV1 $notificationRepositoryV1)
    {
    }

    public function sentRequest($data)
    {
        $data = $this->friendRequestRepositoryV1->setSenderId(Auth::id())
            ->setReceiverId($data['receiver_id'])
            ->setStatus(Config::get('variable_constants.friend_request_status.pending'))
            ->setCreatedAt(Carbon::now())
            ->store();

        SendNotificationJob::dispatch($data, Config::get('variable_constants.notification_types.friend_request'));

        return $data;
    }

    public function getRequests()
    {
        return $this->friendRequestRepositoryV1->setReceiverId(Auth::id())
            ->setSenderId(Auth::id())
            ->setStatus(Config::get('variable_constants.friend_request_status.pending'))
            ->setPage(request()->get('page'))->getRequests();

    }

    public function deleteRequest($requestId)
    {
        $response = $this->friendRequestRepositoryV1->setId($requestId)->setSenderId(Auth::id())->deleteRequest();
        if ($response) {
            DeleteNotificationJob::dispatch($requestId, Config::get('variable_constants.notification_types.friend_request'));
        }
    }

    public function respondRequest($requestId, $data)
    {
        $response = $this->friendRequestRepositoryV1->setId($requestId)->setReceiverId(Auth::id())
            ->setStatus(($data['status'] == 1) ? Config::get('variable_constants.friend_request_status.accept') : Config::get('variable_constants.friend_request_status.decline'))
            ->updateStatus();
        // TODO:: Update Notification Status
        if ($response) {
            UpdateNotificationJob::dispatch(Config::get('variable_constants.notification_types.friend_request'), $requestId, $data['status']);
        }

        return $response ?: 'User not authorized to accept or decline request';
    }

    public function getFriendSuggestions()
    {
        return $this->friendRequestRepositoryV1->setUserId(Auth::id())->setPage(request()->get('page'))->getFriendSuggestions();
    }
}
