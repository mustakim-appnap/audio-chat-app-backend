<?php

namespace App\Repositories\v1;

use App\Enums\Tables;
use App\Helpers\FunctionHelper;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class PrivateChannelRepositoryV1
{
    private $id;

    private $user_id;

    private $name;

    private $frequency;

    private $page_no;

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function setUserId($user_id)
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function setFrequency($frequency)
    {
        $this->frequency = $frequency;

        return $this;
    }

    public function setPage($page_no)
    {
        $this->page_no = $page_no;

        return $this;
    }

    public function store()
    {
        try {
            DB::beginTransaction();
            $channelId = DB::table(Tables::PRIVATE_CHANNELS)->insertGetId([
                'user_id' => $this->user_id,
                'name' => $this->name,
                'frequency' => $this->frequency,
                'status' => Config::get('variable_constants.activation.active'),
                'created_at' => Carbon::now(),
            ]);
            DB::table('private_channel_members')->insert([
                'private_channel_id' => $channelId,
                'user_id' => $this->user_id,
                'status' => Config::get('variable_constants.activation.active'),
                'created_at' => Carbon::now(),
            ]);
            DB::commit();

            return $channelId;
        } catch (Exception $e) {
            DB::rollBack();

            return $e->getMessage();
        }

    }

    public function update()
    {
        return DB::table(Tables::PRIVATE_CHANNELS)
            ->where('id', $this->id)
            ->update([
                'name' => $this->name,
                'updated_at' => Carbon::now(),
            ]);
    }

    public function ownedChannels()
    {
        return DB::table(Tables::PRIVATE_CHANNELS)
            ->select('id', 'name', 'frequency', 'total_members')
            ->where('user_id', $this->user_id)
            ->where('status', Config::get('variable_constants.activation.active'))
            ->whereNull('deleted_at')
            ->orderBy('id', 'asc')
            ->get();
    }

    public function delete()
    {
        $channel = DB::table(Tables::PRIVATE_CHANNELS)->where('id', '=', $this->id)->whereNull('deleted_at')->first();
        if ($channel && $channel->user_id == $this->user_id) {
            return DB::table(Tables::PRIVATE_CHANNELS)->where('id', '=', $this->id)->update([
                'deleted_at' => Carbon::now(),
            ]);
        } else {
            return 'UnAuthorized';
        }

    }

    public function joinedChannels()
    {
        return DB::table(Tables::PRIVATE_CHANNEL_MEMBERS.' AS pcm')
            ->join(Tables::PRIVATE_CHANNELS.' as pc', 'pc.id', '=', 'pcm.private_channel_id')
            ->select('pc.id', 'pc.name', 'pc.frequency', 'pc.total_members')
            ->where('pcm.user_id', '=', $this->user_id)
            ->where('pcm.status', '=', Config::get('variable_constants.activation.active'))
            ->whereNull('pcm.deleted_at')
            ->whereNot('pc.user_id', $this->user_id)
            ->get();
    }

    public function deleteMember()
    {
        try {
            $this->deleteChannelInvitation();

            $channelMember = DB::table(Tables::PRIVATE_CHANNEL_MEMBERS)->where('private_channel_id', '=', $this->id)
                ->where('user_id', '=', $this->user_id)->first();
            if ($channelMember) {
                DB::beginTransaction();
                $leaveChannel = DB::table(Tables::PRIVATE_CHANNEL_MEMBERS)->where('private_channel_id', '=', $this->id)
                    ->where('user_id', '=', $this->user_id)->delete();
                DB::table(Tables::PRIVATE_CHANNELS)->where('id', '=', $this->id)->decrement('total_members', 1);
                DB::commit();

                return $leaveChannel;
            } else {
                return 'Already left the channel';
            }
        } catch (Exception $e) {
            DB::rollBack();

            return $e->getMessage();
        }
    }

    public function getChannelDetails()
    {
        return DB::table(Tables::PRIVATE_CHANNELS.' as pc')
            ->where('pc.id', '=', $this->id)
            ->join(Tables::USERS.' as u', 'u.id', '=', 'pc.user_id')
            ->select('pc.id', 'pc.name', 'pc.frequency', 'pc.total_members',
                DB::raw('CASE WHEN pc.user_id != "'.$this->user_id.'" THEN u.username ELSE CONCAT(u.username, "(You)") END as owned_by'),
                DB::raw('CASE WHEN pc.user_id = "'.$this->user_id.'" THEN 1 ELSE 0 END as is_owner'))
            ->first();
    }

    public function getChannelMembers()
    {
        $filePath = FunctionHelper::asset_base_url(Config::get('path_constants.user_avatar'));
        $this->page_no = max($this->page_no, 1);
        $offset = ($this->page_no - 1) * Config::get('variable_constants.pagination.channel_members');

        $data = DB::table(Tables::PRIVATE_CHANNEL_MEMBERS.' as pcm')->where('pcm.private_channel_id', '=', $this->id)
            ->select('u.id as user_id', DB::raw('CASE WHEN u.id = "'.$this->user_id.'" THEN "MySelf" ELSE u.username END as username'),
                DB::raw('IFNULL(CONCAT("'.$filePath.'" , u.avatar), null) as avatar'),
                'u.is_active', 'u.is_premium', DB::raw('IF(COUNT(f.friend_id) > 0, 1, 0) as is_friend'),
                DB::raw('CASE WHEN fr.sender_id = "'.$this->user_id.'" THEN 1 ELSE 0 END AS is_request_sender'),
                DB::raw('IF(COUNT(fr.sender_id) > 0, 1, 0) as request_status'),
                'fr.id as request_id'
            )
            ->join(Tables::USERS.' as u', 'pcm.user_id', '=', 'u.id')
            ->leftJoin('friends as f', function ($query) {
                $query->on('pcm.user_id', '=', 'f.friend_id')
                    ->whereNull('f.deleted_at')
                    ->where('f.user_id', '=', $this->user_id)
                    ->where('f.status', '=', Config::get('variable_constants.activation.active'));
            })
            ->leftJoin('friend_requests as fr', function ($join) {
                $join->on('pcm.user_id', '=', 'fr.sender_id')
                    ->where('fr.receiver_id', '=', $this->user_id) // Join condition when current user is sender
                    ->where('fr.status', '=', Config::get('variable_constants.activation.inactive'))
                    ->orWhere(function ($query) {
                        $query->on('pcm.user_id', '=', 'fr.receiver_id')
                            ->where('fr.sender_id', '=', $this->user_id) // Join condition when current user is receiver
                            ->where('fr.status', '=', Config::get('variable_constants.activation.inactive'));
                    });
            })
            ->when($this->page_no, function ($condition) use ($offset) {
                return $condition->limit(Config::get('variable_constants.pagination.channel_members'))->offset($offset);
            })
            ->groupBy('pcm.user_id')
            ->whereNull('pcm.deleted_at')
            ->whereNull('u.deleted_at')
            ->get();

        return $data;
    }

    public function deleteChannelInvitation()
    {
        return DB::table(Tables::PRIVATE_CHANNEL_INVITATIONS)
            ->where(['private_channel_id' => $this->id, 'receiver_id' => $this->user_id, 'status' => Config::get('variable_constants.check.yes')])
            ->whereNull('deleted_at')
            ->update(['deleted_at' => Carbon::now()]);
    }
}
