<?php

namespace App\Http\Controllers\v1\Channel;

use App\Http\Controllers\Controller;
use App\Http\Requests\RespondChannelInvitation;
use App\Http\Requests\SendInvitationRequest;
use App\Services\v1\PrivateChannelServiceV1;
use Illuminate\Http\Response;

class ChannelInvitationControllerV1 extends Controller
{
    public function __construct(protected PrivateChannelServiceV1 $privateChannelServiceV1)
    {

    }

    public function sendInvitation(SendInvitationRequest $request)
    {
        try {
            $response = $this->privateChannelServiceV1->sendInvitation($request->validated());
            if ($response) {
                return response(['success' => true, 'error' => null], Response::HTTP_OK);
            } else {
                return response(['success' => false, 'error' => $response], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function respondInvitation(int $invitationId, RespondChannelInvitation $request)
    {
        try {
            $response = $this->privateChannelServiceV1->respondInvitation($invitationId, $request->validated());
            if (is_bool($response)) {
                return response(['success' => true, 'error' => null], Response::HTTP_OK);
            } else {
                return response(['success' => false, 'error' => $response], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
