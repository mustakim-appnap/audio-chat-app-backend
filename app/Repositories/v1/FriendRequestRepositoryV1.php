<?php

namespace App\Repositories\v1;

use App\Enums\Tables;
use App\Helpers\FunctionHelper;
use App\Models\Friend;
use App\Models\FriendRequest;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class FriendRequestRepositoryV1
{
    private $sender_id;

    private $receiver_id;

    private $status;

    private $created_at;

    private $id;

    private $user_id;

    private $page_no;

    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }

    public function setSenderId($sender_id): self
    {
        $this->sender_id = $sender_id;

        return $this;
    }

    public function setReceiverId($receiver_id): self
    {
        $this->receiver_id = $receiver_id;

        return $this;
    }

    public function setUserId($user_id)
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function setStatus($status): self
    {
        $this->status = $status;

        return $this;
    }

    public function setCreatedAt($created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function setPage($page_no)
    {
        $this->page_no = $page_no;

        return $this;
    }

    public function store(): FriendRequest
    {
        return FriendRequest::create([
            'sender_id' => $this->sender_id,
            'receiver_id' => $this->receiver_id,
            'status' => $this->status,
            'created_at' => $this->created_at,
        ]);

    }

    public function getRequests(): Collection
    {
        $filePath = FunctionHelper::asset_base_url(Config::get('path_constants.user_avatar'));
        $this->page_no = max($this->page_no, 1);
        $offset = ($this->page_no - 1) * Config::get('variable_constants.pagination.friend_requests');

        return DB::table(Tables::FRIEND_REQUESTS.' as fr')
            ->select('fr.id as request_id', 'u.id as user_id', 'u.username', 'u.is_active', 'fr.status', DB::raw('IFNULL(CONCAT("'.$filePath.'" , u.avatar), null) as avatar'),
                DB::raw('CASE WHEN fr.sender_id = "'.$this->sender_id.'" THEN 1 ELSE 0 END AS is_request_sender'))
            ->join(Tables::USERS.' as u', function ($join) {
                $join->on('u.id', '=', 'fr.sender_id')
                    ->where('fr.receiver_id', '=', $this->receiver_id) // Join condition when current user is sender
                    ->where('fr.status', '=', $this->status)
                    ->orWhere(function ($query) {
                        $query->on('u.id', '=', 'fr.receiver_id')
                            ->where('fr.sender_id', '=', $this->receiver_id) // Join condition when current user is receiver
                            ->where('fr.status', '=', $this->status);
                    });
            })
            ->whereNull('fr.deleted_at')
            ->whereNull('u.deleted_at')
            ->when($this->page_no, function ($condition) use ($offset) {
                return $condition->limit(Config::get('variable_constants.pagination.friend_requests'))->offset($offset);
            })
            ->get();
    }

    public function deleteRequest(): bool
    {
        return FriendRequest::where('id', $this->id)->where('sender_id', $this->sender_id)->delete();
    }

    public function updateStatus(): bool
    {
        $request = FriendRequest::where('id', '=', $this->id)
            ->where('receiver_id', '=', $this->receiver_id)
            ->where('status', '=', Config::get('variable_constants.friend_request_status.pending'))
            ->first();

        if ($request) {
            DB::beginTransaction();

            $status = $request->update([
                'status' => $this->status,
                'updated_at' => Carbon::now(),
            ]);

            if ($this->status == Config::get('variable_constants.friend_request_status.accept')) {
                $this->addFriend($request);
            }

            DB::commit();

            return $status;
        }

        return false;
    }

    private function addFriend($friendShip): bool
    {
        $data = [
            [
                'user_id' => $friendShip->sender_id,
                'friend_id' => $friendShip->receiver_id,
                'created_at' => Carbon::now(),
            ],
            [
                'user_id' => $friendShip->receiver_id,
                'friend_id' => $friendShip->sender_id,
                'created_at' => Carbon::now(),
            ],

        ];

        return Friend::insert($data);
    }

    public function getRequestStatus()
    {
        return DB::table(Tables::FRIEND_REQUESTS)
            ->select('id as request_id',
                DB::raw('CASE WHEN sender_id = "'.$this->sender_id.'" THEN 1 ELSE 0 END AS is_request_sender'),
                DB::raw('IF(COUNT(sender_id) > 0, 1, 0) as request_status'))
            ->where(function ($query) {
                $query->where('sender_id', $this->sender_id)
                    ->where('receiver_id', $this->receiver_id)
                    ->where('status', Config::get('variable_constants.activation.inactive'))
                    ->whereNull('deleted_at');
            })
            ->orWhere(function ($query) {
                $query->where('receiver_id', $this->sender_id)
                    ->where('sender_id', $this->receiver_id)
                    ->where('status', Config::get('variable_constants.activation.inactive'))
                    ->whereNull('deleted_at');
            })
            ->first();
    }

    public function getFriendSuggestions()
    {
        $avatarFilePath = FunctionHelper::asset_base_url(Config::get('path_constants.user_avatar'));
        $this->page_no = max($this->page_no, 1);
        $offset = ($this->page_no - 1) * Config::get('variable_constants.pagination.friend_suggestions');

        $joinedChannels = DB::table('user_channel_join_history')->where('user_id', '=', $this->user_id)->orderBy('id', 'DESC')->limit(10)->pluck('channel_id')->toArray();
        $friendIds = DB::table('friends')->where('user_id', '=', $this->user_id)
            ->where('status', '=', Config::get('variable_constants.activation.active'))
            ->whereNull('deleted_at')
            ->pluck('friend_id')
            ->toArray();
        $friendRequests = DB::table('friend_requests')
            ->where('sender_id', '=', $this->user_id)
            ->orWhere('receiver_id', '=', $this->user_id)
            ->where('status', '=', Config::get('variable_constants.activation.inactive'))
            ->whereNull('deleted_at')
            ->get();

        $requestedUserIds = $friendRequests->map(function ($request) {
            return $request->sender_id == $this->user_id ? $request->receiver_id : $request->sender_id;
        })->all();

        // return DB::table('user_channel_join_history as ucjh')
        //     ->select('u.id as user_id', 'u.username', 'u.is_active', 'u.status', DB::raw('IFNULL(CONCAT("'.$avatarFilePath.'" , u.avatar), null) as avatar'))
        //     ->join('users as u', function ($query) {
        //         $query->on('u.id', '=', 'ucjh.user_id')
        //             ->whereNull('u.deleted_at');
        //     })
        //     ->where('ucjh.user_id', '!=', $this->user_id)
        //     ->whereIn('ucjh.channel_id', $joinedChannels)
        //     ->whereNotIn('ucjh.user_id', $friendIds)
        //     ->whereNotIn('ucjh.user_id', $requestedUserIds)
        //     ->get();

        $subquery = DB::table('user_channel_join_history as ucjh')
            ->select(
                'ucjh.user_id',
                'ucjh.channel_id',
                DB::raw('ROW_NUMBER() OVER (PARTITION BY ucjh.channel_id ORDER BY ucjh.created_at DESC) as row_num')
            )
            ->whereIn('ucjh.channel_id', $joinedChannels)
            ->where('ucjh.user_id', '!=', $this->user_id)
            ->whereNotIn('ucjh.user_id', $friendIds)
            ->whereNotIn('ucjh.user_id', $requestedUserIds);

        // Main query to join with users and apply final selections
        $users = DB::table(DB::raw("({$subquery->toSql()}) as sub"))
            ->mergeBindings($subquery) // Ensure bindings are correctly applied
            ->select('u.id as user_id', 'u.username', 'u.is_active', 'u.status', DB::raw('IFNULL(CONCAT("'.$avatarFilePath.'" , u.avatar), null) as avatar'))
            ->join('users as u', function ($join) {
                $join->on('u.id', '=', 'sub.user_id')
                    ->whereNull('u.deleted_at');
            })
            ->where('sub.row_num', '<=', Config::get('variable_constants.suggestions.users_per_channel'))
            ->distinct('u.id')
            ->when($this->page_no, function ($condition) use ($offset) {
                return $condition->limit(Config::get('variable_constants.pagination.friend_suggestions'))->offset($offset);
            })
            ->get();

        return $users;

    }
}
