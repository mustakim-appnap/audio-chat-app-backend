<?php

namespace App\Services\v1;

use App\Repositories\v1\ChatRepositoryV1;
use App\Repositories\v1\ChatThreadRepositoryV1;
use Illuminate\Support\Facades\Auth;

class ChatServiceV1
{
    public function __construct(protected ChatThreadRepositoryV1 $chatThreadRepositoryV1, protected ChatRepositoryV1 $chatRepositoryV1)
    {

    }

    public function getChatThreads()
    {
        return $this->chatThreadRepositoryV1->setSenderId(Auth::id())->setReceiverId(Auth::id())->getChatThreads();
    }
}
