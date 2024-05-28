<?php

namespace App\Http\Controllers\v1\Friends;

use App\Http\Controllers\Controller;
use App\Http\Requests\RespondFriendRequest;
use App\Http\Requests\SentFriendRequest;
use App\Services\v1\FriendRequestServiceV1;
use Illuminate\Http\Response;

class FriendRequestControllerV1 extends Controller
{
    public function __construct(protected FriendRequestServiceV1 $friendRequestServiceV1)
    {

    }

    public function sentRequest(SentFriendRequest $request)
    {
        try {
            $response = $this->friendRequestServiceV1->sentRequest($request->validated());
            if ($response) {
                return response(['success' => true, 'error' => null], Response::HTTP_OK);
            } else {
                return response(['success' => false, 'error' => $response], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getRequests()
    {
        try {
            $response = $this->friendRequestServiceV1->getRequests();
            if ($response) {
                return response(['success' => true, 'error' => null, 'data' => $response], Response::HTTP_OK);
            } else {
                return response(['success' => false, 'error' => $response], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deleteRequest(int $requestId)
    {
        try {
            $response = $this->friendRequestServiceV1->deleteRequest($requestId);
            if ($response) {
                return response(['success' => true, 'error' => null], Response::HTTP_OK);
            } else {
                return response(['success' => false, 'error' => $response], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function respondRequest(int $requestId, RespondFriendRequest $request)
    {
        try {
            $response = $this->friendRequestServiceV1->respondRequest($requestId, $request->validated());
            if (is_bool($response)) {
                return response(['success' => true, 'error' => null], Response::HTTP_OK);
            } else {
                return response(['success' => false, 'error' => $response], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getFriendSuggestions()
    {
        try {
            $response = $this->friendRequestServiceV1->getFriendSuggestions();
            if ($response) {
                return response(['success' => true, 'error' => null, 'data' => $response], Response::HTTP_OK);
            } else {
                return response(['success' => false, 'error' => $response], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
