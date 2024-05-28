<?php

namespace App\Repositories\v1;

use App\Enums\Tables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class FavouriteChannelRepositoryV1
{
    private $channel_frequency;

    private $channel_type;

    private $user_id;

    private $channel_id;

    public function setChannelFrequency($channel_frequency)
    {
        $this->channel_frequency = $channel_frequency;

        return $this;
    }

    public function setChannelType($channel_type)
    {
        $this->channel_type = $channel_type;

        return $this;
    }

    public function setUserId($user_id)
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function setChannelId()
    {
        if ($this->channel_type == Config::get('variable_constants.channel_types.public')) {
            $channel = DB::table('public_channels')->where('frequency', $this->channel_frequency)->first('id');
        } else {
            $channel = DB::table('private_channels')->where('frequency', $this->channel_frequency)->first('id');
        }

        $this->channel_id = $channel->id;

        return $this;
    }

    public function index()
    {
        return DB::table(Tables::FAVOURITE_CHANNELS.' as fc')->select('fc.channel_id', 'fc.channel_frequency', 'fc.channel_type')
            ->where('fc.channel_type', $this->channel_type)
            ->where('fc.user_id', $this->user_id)
            ->when($this->channel_type == Config::get('variable_constants.channel_types.private'), function ($query) {
                $query->join('private_channels as pc', 'pc.id', '=', 'fc.channel_id')
                    ->join('users as u', 'u.id', '=', 'pc.user_id')
                    ->select('pc.name', 'pc.frequency', 'pc.id as channel_id', 'pc.total_members', 'fc.channel_type', 'u.username as owner');
            })
            ->when($this->channel_type == Config::get('variable_constants.channel_types.public'), function ($query) {
                $query->select(DB::raw('null as name'), 'fc.channel_frequency as frequency', 'fc.channel_id', DB::raw('0 as total_members'), 'fc.channel_type');
            })
            ->get();
    }

    public function store()
    {
        return DB::table(Tables::FAVOURITE_CHANNELS)
            ->insert([
                'channel_id' => $this->channel_id,
                'channel_frequency' => $this->channel_frequency,
                'channel_type' => $this->channel_type,
                'user_id' => $this->user_id,
                'created_at' => Carbon::now(),
            ]);
    }

    public function checkFavouriteChannel()
    {
        return DB::table(Tables::FAVOURITE_CHANNELS)
            ->where('user_id', $this->user_id)
            ->where('channel_frequency', $this->channel_frequency)
            ->first();
    }

    public function delete()
    {
        return DB::table(Tables::FAVOURITE_CHANNELS)
            ->where('user_id', $this->user_id)
            ->where('channel_frequency', $this->channel_frequency)
            ->where('channel_type', $this->channel_type)
            ->delete();
    }
}
