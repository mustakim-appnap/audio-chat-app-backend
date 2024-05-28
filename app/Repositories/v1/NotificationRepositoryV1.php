<?php

namespace App\Repositories\v1;

use App\Helpers\FunctionHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class NotificationRepositoryV1
{
    private $notification_type;

    private $sender_id;

    private $receiver_id;

    private $data_id;

    private $title;

    private $page_no;

    public function setNotificationType($notification_type)
    {
        $this->notification_type = $notification_type;

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

    public function setDataId($data_id)
    {
        $this->data_id = $data_id;

        return $this;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function setPage($page_no)
    {
        $this->page_no = $page_no;

        return $this;
    }

    public function store()
    {
        return DB::table('notifications')
            ->insert([
                'notification_type' => $this->notification_type,
                'sender_id' => $this->sender_id,
                'receiver_id' => $this->receiver_id,
                'data_id' => $this->data_id,
                'title' => $this->title,
                'status' => Config::get('variable_constants.activation.inactive'),
                'created_at' => Carbon::now(),
            ]);
    }

    public function updateNotificationStatus($requestStatus)
    {
        return DB::table('notifications')
            ->where('notification_type', '=', $this->notification_type)
            ->where('data_id', '=', $this->data_id)
            ->update([
                'status' => Config::get('variable_constants.check.yes'),
                'message' => $requestStatus == 1 ? 'Request accepted' : 'Request rejected',
                'updated_at' => Carbon::now(),
            ]);
    }

    public function getUserNotifications()
    {
        $this->page_no = max($this->page_no, 1);
        $offset = ($this->page_no - 1) * Config::get('variable_constants.pagination.notifications');
        $filePath = FunctionHelper::asset_base_url(Config::get('path_constants.user_avatar'));

        return DB::table('notifications as n')
            ->select('u.username as username', DB::raw(DB::raw('IFNULL(CONCAT("'.$filePath.'" , u.avatar), null) as avatar')), 'n.title', 'n.message', 'n.notification_type', 'n.data_id', 'n.status', DB::raw('DATE_FORMAT(n.created_at, "%d/%m/%Y") as created_at'))
            ->join('users as u', 'u.id', '=', 'n.sender_id')
            ->where('n.receiver_id', '=', $this->receiver_id)
            ->when($this->page_no, function ($condition) use ($offset) {
                return $condition->limit(Config::get('variable_constants.pagination.notifications'))->offset($offset);
            })
            ->get();
    }

    public function delete()
    {
        return DB::table('notifications')->where('notification_type', '=', $this->notification_type)
            ->where('data_id', '=', $this->data_id)->delete();
    }
}
