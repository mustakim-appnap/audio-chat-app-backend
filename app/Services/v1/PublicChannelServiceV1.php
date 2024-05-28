<?php

namespace App\Services\v1;

use App\Repositories\v1\PublicChannelRepositoryV1;
use Illuminate\Support\Facades\Config;

class PublicChannelServiceV1
{
    public function __construct(protected PublicChannelRepositoryV1 $publicChannelRepositoryV1)
    {

    }

    public function getActiveChannel()
    {
        $data = $this->publicChannelRepositoryV1->getActiveChannel();
        if ($data) {
            $data->channel = sprintf('%05.2f', $data->channel);
        }

        return $data ? $data : [
            'channel' => Config::get('variable_constants.default.public_channel'),
            'user_count' => 1,
        ];
    }
}
