<?php

namespace App\Repositories\v1;

use App\Enums\Tables;
use App\Helpers\FunctionHelper;
use App\Models\Friend;
use App\Models\FriendRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class FriendRepositoryV1
{
    private $user_id;

    private $friend_id;

    // @phpstan-ignore-next-line
    private $status;

    // @phpstan-ignore-next-line
    private $created_at;

    private $page_no;

    public function setUserId($user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function setFriendId($friend_id): self
    {
        $this->friend_id = $friend_id;

        return $this;
    }

    public function setStatus($status): self
    {
        $this->status = $status;

        return $this;
    }

    public function setPage($page_no)
    {
        $this->page_no = $page_no;

        return $this;
    }

    public function getFriends()
    {
        $filePath = FunctionHelper::asset_base_url(Config::get('path_constants.user_avatar'));
        $this->page_no = max($this->page_no, 1);
        $offset = ($this->page_no - 1) * Config::get('variable_constants.pagination.friends');

        return DB::table(Tables::FRIENDS.' as f')->where('user_id', '=', $this->user_id)
            ->select('f.id', 'u.id as user_id', 'u.username', 'u.is_active', 'f.status', DB::raw('IFNULL(CONCAT("'.$filePath.'" , u.avatar), null) as avatar'))
            ->leftJoin(Tables::USERS.' as u', 'f.friend_id', '=', 'u.id')
            ->where('f.status', Config::get('variable_constants.activation.active'))
            ->whereNull('f.deleted_at')
            ->whereNull('u.deleted_at')
            ->when($this->page_no, function ($condition) use ($offset) {
                return $condition->limit(Config::get('variable_constants.pagination.friends'))->offset($offset);
            })
            ->get();
    }

    public function deleteFriendship()
    {
        DB::beginTransaction();
        $deleteFriend = Friend::whereIn('user_id', [$this->user_id, $this->friend_id])
            ->whereIn('friend_id', [$this->user_id, $this->friend_id])
            ->delete();
        FriendRequest::whereIn('sender_id', [$this->user_id, $this->friend_id])->whereIn('receiver_id', [$this->user_id, $this->friend_id])
            ->delete();
        DB::commit();

        return $deleteFriend;
    }

    public function checkRelationWithAuthUser()
    {

        $filePath = FunctionHelper::asset_base_url(Config::get('path_constants.user_avatar'));

        $friendRequest = DB::table(Tables::FRIEND_REQUESTS.' as fr')
            ->select('fr.id as request_id', 'u.id as user_id', 'u.username', 'u.is_active', 'fr.status as request_status',
                DB::raw('IFNULL(CONCAT("'.$filePath.'" , u.avatar), null) as avatar'), DB::raw('DATE_FORMAT(u.created_at, "%d/%m/%Y") as created_at'),
                DB::raw('CASE WHEN fr.sender_id = "'.$this->user_id.'" THEN 1 ELSE 0 END AS is_request_sender'))
            ->join(Tables::USERS.' as u', 'u.id', '=', 'fr.receiver_id')
            ->where(function ($query) {
                $query->where('fr.sender_id', '=', $this->user_id)
                    ->where('fr.receiver_id', '=', $this->friend_id);
            })
            ->orWhere(function ($query) {
                $query->where('fr.sender_id', '=', $this->friend_id)
                    ->where('fr.receiver_id', '=', $this->user_id);
            })
                // ->where('fr.status', '=', Config::get('variable_constants.activation.inactive'))
            ->whereNull('fr.deleted_at')
            ->first();
        if ($friendRequest && $friendRequest->request_status == Config::get('variable_constants.activation.active')) {
            $response = DB::table(Tables::FRIENDS.' as f')->where('user_id', '=', $this->user_id)
                ->select('u.id as user_id', 'u.username', 'u.is_active', 'f.status as is_friend', DB::raw('IFNULL(CONCAT("'.$filePath.'" , u.avatar), null) as avatar'), DB::raw('DATE_FORMAT(u.created_at, "%d/%m/%Y") as created_at'))
                ->leftJoin('users as u', 'f.friend_id', '=', 'u.id')
                ->where('friend_id', '=', $this->friend_id)
                ->where('f.status', Config::get('variable_constants.activation.active'))
                ->whereNull('f.deleted_at')
                ->first();
        } elseif ($friendRequest && $friendRequest->request_status == Config::get('variable_constants.activation.inactive')) {
            $response = $friendRequest;
            $response->is_friend = Config::get('variable_constants.check.no');
            $response->request_status = Config::get('variable_constants.check.yes');
        } else {
            $response = DB::table(Tables::USERS)->where('id', $this->friend_id)->select('id as user_id', 'username', 'is_active', DB::raw('IFNULL(CONCAT("'.$filePath.'" , avatar), null) as avatar'), 'created_at')
                ->first();
            $response->is_friend = Config::get('variable_constants.check.no');
            $response->request_status = Config::get('variable_constants.check.no');
            $response->is_request_sender = Config::get('variable_constants.check.no');
        }

        return $response;
    }
}
