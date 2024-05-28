<?php

namespace App\Repositories\v1;

use App\Enums\Tables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatThreadRepositoryV1
{
    private $id;

    private $sender_id;

    private $receiver_id;

    private $content;

    private $is_seen;

    public function setThreadId($id): self
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

    public function getChatThreads()
    {
        return DB::table(Tables::CHAT_THREADS.' as ct')
            ->select('ct.id as thread_id', DB::raw('CASE WHEN ct.sender_id = "'.Auth::id().'" THEN receiver.username ELSE sender.username END as username'),
                'sender.id as user_id', 'ct.content as message', 'ct.is_seen', 'ct.unseen_message_count', 'ct.updated_at as sent_at')
            ->where(function ($query) {
                $query->where('ct.sender_id', '=', $this->sender_id)
                    ->orWhere('ct.receiver_id', '=', $this->receiver_id);
            })
            ->join('users as sender', 'ct.sender_id', '=', 'sender.id')
            ->join('users as receiver', 'ct.receiver_id', '=', 'receiver.id')
            ->whereNull('ct.deleted_at')
            ->get();
    }
}
