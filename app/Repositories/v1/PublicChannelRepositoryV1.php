<?php

namespace App\Repositories\v1;

use App\Enums\Tables;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class PublicChannelRepositoryV1
{
    public function getActiveChannel()
    {
        return DB::table(Tables::PUBLIC_ACTIVE_CHANNELS)
            ->select('channel_id as channel', 'user_count')
            ->where('is_active', Config::get('variable_constants.activation.active'))
            ->where('user_count', '<', Config::get('variable_constants.public_channel.max_user'))
            ->orderBy('channel_id', 'asc')
            ->first();
    }
}
