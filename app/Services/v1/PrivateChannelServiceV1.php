<?php

namespace App\Services\v1;

use App\Jobs\SendNotificationJob;
use App\Jobs\UpdateNotificationJob;
use App\Repositories\v1\PrivateChannelInviteRepositoryV1;
use App\Repositories\v1\PrivateChannelRepositoryV1;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class PrivateChannelServiceV1
{
    public function __construct(protected PrivateChannelRepositoryV1 $privateChannelRepositoryV1, protected PrivateChannelInviteRepositoryV1 $privateChannelInviteRepositoryV1)
    {

    }

    public function createChannel($data)
    {
        $channel = $this->privateChannelRepositoryV1
            ->setUserId(Auth::id())
            ->setName($data['name'])
            ->setFrequency($data['frequency'])
            ->store();
        if ($channel) {
            return [
                'channel_id' => $channel,
            ];
        } else {
            return 'An error occurred!';
        }
    }

    public function editChannel($channelId, $data)
    {
        return $this->privateChannelRepositoryV1
            ->setId($channelId)
            ->setName($data['name'])
            ->update();
    }

    public function ownedAndJoinedChannels()
    {
        $data['owned'] = $this->privateChannelRepositoryV1->setUserId(Auth::id())->ownedChannels();

        return $data;
    }

    public function getUserPrivateChannels()
    {
        return $this->privateChannelRepositoryV1->setUserId(Auth::id())->ownedChannels();
    }

    public function sendInvitation($data)
    {
        $invitationExists = $this->privateChannelInviteRepositoryV1->setChannelId($data['channel_id'])
            ->setSenderId(Auth::id())
            ->setReceiverId($data['receiver_id'])
            ->checkInvitationExists();
        if (! $invitationExists) {
            $response = $this->privateChannelInviteRepositoryV1->setChannelId($data['channel_id'])
                ->setSenderId(Auth::id())
                ->setReceiverId($data['receiver_id'])
                ->store();
            if ($response) {
                //TODO:: Send Notification
                SendNotificationJob::dispatch($response, Config::get('variable_constants.notification_types.channel_invitation'));
            }

            return $response;
        } else {
            return 'Already send invitation';
        }
    }

    public function respondInvitation($invitationId, $data)
    {
        $response = $this->privateChannelInviteRepositoryV1->setId($invitationId)->setReceiverId(Auth::id())->setStatus($data['status'])->respondInvitation();
        if ($response) {
            UpdateNotificationJob::dispatch(Config::get('variable_constants.notification_types.channel_invitation'), $invitationId, $data['status']);
        }

        return $response;
    }

    public function deleteChannel($channelId)
    {
        return $this->privateChannelRepositoryV1->setId($channelId)->setUserId(Auth::id())->delete();
    }

    public function getOwnedAndJoinedChannels()
    {
        $data['owned'] = $this->privateChannelRepositoryV1->setUserId(Auth::id())->ownedChannels();
        $data['joined'] = $this->privateChannelRepositoryV1->setUserId(Auth::id())->joinedChannels();

        return $data;
    }

    public function leaveChannel($data)
    {
        return $this->privateChannelRepositoryV1->setId($data['channel_id'])->setUserId(Auth::id())->deleteMember();
    }

    public function kickChannelMember($data)
    {
        return $this->privateChannelRepositoryV1->setId($data['channel_id'])->setUserId($data['user_id'])->deleteMember();
    }

    public function getChannelMembers($channelId)
    {
        $data = $this->privateChannelRepositoryV1->setId($channelId)->setUserId(Auth::id())->getChannelDetails();
        $data->members = $this->privateChannelRepositoryV1->setId($channelId)->setUserId(Auth::id())->setPage(request()->get('page'))->getChannelMembers();

        return $data;
    }
}
