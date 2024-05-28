<?php

namespace App\Http\Controllers\v1\Channel;

use App\Http\Controllers\Controller;
use App\Services\v1\PublicChannelServiceV1;
use Illuminate\Http\Response;

class PublicChannelControllerV1 extends Controller
{
    public function __construct(protected PublicChannelServiceV1 $publicChannelServiceV1)
    {

    }

    public function shuffleChannel()
    {
        try {
            $response = $this->publicChannelServiceV1->getActiveChannel();
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
