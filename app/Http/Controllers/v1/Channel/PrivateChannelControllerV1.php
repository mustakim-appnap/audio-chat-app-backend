<?php

namespace App\Http\Controllers\v1\Channel;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckAvailableFrequency;
use App\Http\Requests\CreatePrivateChannelRequest;
use App\Http\Requests\EditPrivateChannel;
use App\Http\Requests\KickChannelMember;
use App\Http\Requests\LeaveChannelRequest;
use App\Services\v1\PrivateChannelServiceV1;
use Illuminate\Http\Response;

class PrivateChannelControllerV1 extends Controller
{
    public function __construct(protected PrivateChannelServiceV1 $privateChannelServiceV1)
    {

    }

    public function index()
    {
        try {
            $response = $this->privateChannelServiceV1->getUserPrivateChannels();
            if ($response) {
                return response(['success' => true, 'error' => null, 'data' => $response], Response::HTTP_OK);
            } else {
                return response(['success' => false, 'error' => $response], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(CreatePrivateChannelRequest $request)
    {
        try {
            $response = $this->privateChannelServiceV1->createChannel($request->validated());
            if (is_array($response)) {
                return response(['success' => true, 'error' => null, 'data' => $response], Response::HTTP_OK);
            } else {
                return response(['success' => false, 'error' => $response], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function edit(int $channelId, EditPrivateChannel $request)
    {
        try {
            $response = $this->privateChannelServiceV1->editChannel($channelId, $request->validated());
            if ($response) {
                return response(['success' => true, 'error' => null], Response::HTTP_OK);
            } else {
                return response(['success' => false, 'error' => $response], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(int $channelId)
    {
        try {
            $response = $this->privateChannelServiceV1->deleteChannel($channelId);
            if (is_int($response)) {
                return response(['success' => true, 'error' => null], Response::HTTP_OK);
            } else {
                return response(['success' => false, 'error' => $response], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function checkFrequency(CheckAvailableFrequency $request)
    {
        if ($request->validated()) {
            return response(['success' => true, 'error' => null], Response::HTTP_OK);
        } else {
            return response(['success' => false, 'error' => $request], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function ownedAndJoinedChannel()
    {
        try {
            $response = $this->privateChannelServiceV1->getOwnedAndJoinedChannels();
            if ($response) {
                return response(['success' => true, 'error' => null, 'data' => $response], Response::HTTP_OK);
            } else {
                return response(['success' => false, 'error' => $response], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function leaveChannel(LeaveChannelRequest $request)
    {
        try {
            $response = $this->privateChannelServiceV1->leaveChannel($request->validated());
            if (is_int($response)) {
                return response(['success' => true, 'error' => null], Response::HTTP_OK);
            } else {
                return response(['success' => false, 'error' => $response], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function kickUser(KickChannelMember $request)
    {
        try {
            $response = $this->privateChannelServiceV1->kickChannelMember($request->validated());
            if (is_int($response)) {
                return response(['success' => true, 'error' => null], Response::HTTP_OK);
            } else {
                return response(['success' => false, 'error' => $response], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getPrivateChannelMembers(int $channelId)
    {
        try {
            $response = $this->privateChannelServiceV1->getChannelMembers($channelId);
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
