<?php

namespace App\Repositories\v1;

use Exception;
use Illuminate\Support\Facades\DB;

class PushNotificationRepositoryV1
{
    private $user_id;

    private $channel_invitation;

    private $friend_request;

    private $message;

    private $promotional;

    private $is_allowed;

    public function setUserId($user_id)
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function setIsAllowed($is_allowed)
    {
        $this->is_allowed = $is_allowed;

        return $this;
    }

    public function setChannelInvitationStatus($channel_invitation)
    {
        $this->channel_invitation = $channel_invitation;

        return $this;
    }

    public function setFriendRequestStatus($friend_request)
    {
        $this->friend_request = $friend_request;

        return $this;
    }

    public function setMessageStatus($message)
    {
        $this->message = $message;

        return $this;
    }

    public function setPromotionalStatus($promotional)
    {
        $this->promotional = $promotional;

        return $this;
    }

    public function getPushNotificationStatus()
    {
        return DB::table('users as u')
            ->select('u.push_notification_status as is_enabled',
                DB::raw('IFNULL(upn.channel_invitation, 1) as channel_invitation'),
                DB::raw('IFNULL(upn.friend_request, 1) as friend_request'),
                DB::raw('IFNULL(upn.message, 1) as message'),
                DB::raw('IFNULL(upn.promotional, 1) as promotional'))
            ->where('u.id', '=', $this->user_id)
            ->leftJoin('user_push_notifications as upn', 'u.id', '=', 'upn.user_id')
            ->first();
    }

    public function update()
    {
        try {
            DB::beginTransaction();
            DB::table('users')->where('id', '=', $this->user_id)->update([
                'push_notification_status' => $this->is_allowed,
            ]);
            $userPushNotifications = DB::table('user_push_notifications')->where('user_id', '=', $this->user_id)->first();
            if ($userPushNotifications) {
                DB::table('user_push_notifications')->where('user_id', '=', $this->user_id)->update([
                    'channel_invitation' => $this->channel_invitation,
                    'friend_request' => $this->friend_request,
                    'message' => $this->message,
                    'promotional' => $this->promotional,
                ]);
            } else {
                DB::table('user_push_notifications')->insert([
                    'user_id' => $this->user_id,
                    'channel_invitation' => $this->channel_invitation,
                    'friend_request' => $this->friend_request,
                    'message' => $this->message,
                    'promotional' => $this->promotional,
                ]);
            }
            DB::commit();

            return $userPushNotifications;
        } catch (Exception $e) {
            DB::rollBack();

            return $e->getMessage();
        }

    }
}
