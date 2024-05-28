<?php

namespace App\Http\Controllers\v1\Friends;

use App\Http\Controllers\Controller;
use App\Services\v1\FriendServiceV1;
use Illuminate\Http\Response;

class FriendControllerV1 extends Controller
{
    public function __construct(protected FriendServiceV1 $friendServiceV1)
    {
    }

    public function friendList()
    {
        try {
            $response = $this->friendServiceV1->friendList();
            if ($response) {
                return response(['success' => true, 'error' => null, 'data' => $response], Response::HTTP_OK);
            } else {
                return response(['success' => false, 'error' => $response], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function unfriend(int $friendId)
    {
        try {
            $response = $this->friendServiceV1->unfriend($friendId);
            if (is_int($response)) {
                return response(['success' => true, 'error' => null], Response::HTTP_OK);
            } else {
                return response(['success' => false, 'error' => $response], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function checkUserRelationship(int $userId)
    {
        try {
            $response = $this->friendServiceV1->checkRelationWithAuthUser($userId);

            return $response;
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
