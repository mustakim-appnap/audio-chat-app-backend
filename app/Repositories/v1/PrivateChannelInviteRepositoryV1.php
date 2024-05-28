<?php

namespace App\Repositories\v1;

use App\Enums\Tables;
use App\Models\PrivateChannelInvitation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class PrivateChannelInviteRepositoryV1
{
    private $id;

    private $sender_id;

    private $receiver_id;

    private $channel_id;

    private $status;

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function setSenderId($sender_id)
    {
        $this->sender_id = $sender_id;

        return $this;
    }

    public function setReceiverId($receiver_id)
    {
        $this->receiver_id = $receiver_id;

        return $this;
    }

    public function setChannelId($channel_id)
    {
        $this->channel_id = $channel_id;

        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    public function store()
    {
        return PrivateChannelInvitation::create([
            'private_channel_id' => $this->channel_id,
            'sender_id' => $this->sender_id,
            'receiver_id' => $this->receiver_id,
            'status' => Config::get('variable_constants.activation.inactive'),
            'created_at' => Carbon::now(),
        ]);
    }

    public function checkInvitationExists()
    {
        return DB::table(Tables::PRIVATE_CHANNEL_INVITATIONS)
            ->where('private_channel_id', $this->channel_id)
            ->where('sender_id', $this->sender_id)
            ->where('receiver_id', $this->receiver_id)
            ->whereNull('deleted_at')
            ->exists();
    }

    public function respondInvitation()
    {
        if ($this->status == Config::get('variable_constants.check.yes')) {
            $invitationData = DB::table(Tables::PRIVATE_CHANNEL_INVITATIONS)->where('id', '=', $this->id)->first();
            if ($invitationData && $invitationData->status == Config::get('variable_constants.check.no')) {
                if ($invitationData->receiver_id == $this->receiver_id) {
                    DB::beginTransaction();
                    DB::table(Tables::PRIVATE_CHANNEL_INVITATIONS)->where('id', '=', $this->id)->update([
                        'status' => $this->status,
                        'updated_at' => Carbon::now(),
                    ]);
                    $this->setChannelId($invitationData->private_channel_id);
                    /**
                     * add member in private_channel_members table
                     */
                    DB::table('private_channel_members')->insert([
                        'private_channel_id' => $this->channel_id,
                        'user_id' => $this->receiver_id,
                        'created_at' => Carbon::now(),
                    ]);
                    /**
                     * Update total_members count in private_channels table
                     */
                    DB::table(Tables::PRIVATE_CHANNELS)
                        ->where('id', '=', $this->channel_id)
                        ->increment('total_members', 1);
                    DB::commit();

                    return true;
                } else {
                    return 'not authorized to accept/decline invitation';
                }

            } else {
                return 'invitation already accepted or declined';
            }

        } else {
            return DB::table(Tables::PRIVATE_CHANNEL_INVITATIONS)->where('id', '=', $this->id)->delete();
        }

    }
}
