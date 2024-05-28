<?php

namespace App\Services\v1;

use App\Repositories\v1\FriendRepositoryV1;
use Illuminate\Support\Facades\Auth;

class FriendServiceV1
{
    public function __construct(protected FriendRepositoryV1 $friendRepositoryV1)
    {
    }

    public function friendList()
    {
        return $this->friendRepositoryV1->setUserId(Auth::id())
            ->setPage(request()->get('page'))->getFriends();
    }

    public function unfriend($friendId)
    {
        $data = $this->friendRepositoryV1->setUserId(Auth::id())
            ->setFriendId($friendId)->deleteFriendship();

        return $data ?: 'No friendship exists';
    }

    public function checkRelationWithAuthUser($userId)
    {
        return $this->friendRepositoryV1->setUserId(Auth::id())->setFriendId($userId)->checkRelationWithAuthUser();
    }
}
