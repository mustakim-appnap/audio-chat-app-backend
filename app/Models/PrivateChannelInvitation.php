<?php

namespace App\Models;

use App\Enums\Tables;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrivateChannelInvitation extends Model
{
    use HasFactory;

    protected $table = Tables::PRIVATE_CHANNEL_INVITATIONS;

    protected $guarded = [];
}
