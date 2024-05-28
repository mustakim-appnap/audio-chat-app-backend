<?php

namespace App\Http\Controllers\v1\Chat;

use App\Http\Controllers\Controller;
use App\Services\v1\ChatServiceV1;
use Illuminate\Http\Response;

class ChatControllerV1 extends Controller
{
    public function __construct(protected ChatServiceV1 $chatServiceV1)
    {

    }

    public function threads()
    {
        try {
            $response = $this->chatServiceV1->getChatThreads();
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
