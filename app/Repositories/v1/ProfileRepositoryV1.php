<?php

namespace App\Repositories\v1;

use App\Helpers\FunctionHelper;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class ProfileRepositoryV1
{
    private $id;

    private $username;

    private $dob;

    private $page_no;

    private $outfit_model;

    private $outfit_image;

    private $avatar;

    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }

    public function setUsername($username): self
    {
        $this->username = $username;

        return $this;
    }

    public function setDob($dob): self
    {
        $this->dob = $dob;

        return $this;
    }

    public function setAvatar($avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function setPage($page_no): self
    {
        $this->page_no = $page_no;

        return $this;
    }

    public function setOutfitModel($outfit_model): self
    {
        $this->outfit_model = $outfit_model;

        return $this;
    }

    public function setOutfitImage($outfit_image): self
    {
        $this->outfit_image = $outfit_image;

        return $this;
    }

    public function delete(): int
    {
        $time = time() * 1000;
        $user = User::findOrFail($this->id);

        return DB::table('users')
            ->where('id', '=', $this->id)
            ->update([
                'auth_id' => $user->auth_id ? $user->auth_id.'_'.'deleted'.'_'.$time : null,
                'device_token' => $user->device_token.'_'.'deleted'.'_'.$time,
                'username' => $user->username ? $user->username.'_'.'deleted'.'_'.$time : null,
                'socket_id' => null,
                'is_active' => Config::get('variable_constants.check.no'),
                'status' => Config::get('variable_constants.check.no'),
                'deleted_at' => date('Y-m-d H:i:s'),
            ]);
    }

    public function checkUsernameExists(): bool
    {
        return DB::table('users')->where('username', '=', $this->username)->exists();
    }

    public function updateBasicInfo(): int
    {
        return DB::table('users')->where('id', '=', $this->id)->update([
            'username' => $this->username,
            'dob' => $this->dob,
            'is_registration_complete' => Config::get('variable_constants.check.yes'),
        ]);
    }

    public function updateUsername(): int
    {
        return DB::table('users')->where('id', '=', $this->id)->update([
            'username' => $this->username,
            'updated_at' => date('Y-m-d H:i:s'),
            'username_updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function getUsersByUsername(): Collection
    {
        $filePath = FunctionHelper::asset_base_url(Config::get('path_constants.user_avatar'));
        $this->page_no = max($this->page_no, 1);
        $offset = ($this->page_no - 1) * Config::get('variable_constants.pagination.friends');

        return DB::table('users as u')
            ->select('u.id as user_id', 'u.username', 'u.is_active', 'u.phone_number',
                DB::raw('IFNULL(CONCAT("'.$filePath.'" , u.avatar), null) as avatar'),
                DB::raw('IF(COUNT(f.friend_id) > 0, 1, 0) as is_friend')
            )
            ->leftJoin('friends as f', function ($query) {
                $query->on('u.id', '=', 'f.friend_id')
                    ->whereNull('f.deleted_at')
                    ->where('f.user_id', '=', $this->id)
                    ->where('f.status', '=', Config::get('variable_constants.activation.active'));
            })
            ->where('u.id', '!=', $this->id)
            ->where('u.status', '=', Config::get('variable_constants.activation.active'))
            ->whereNull('u.deleted_at')
            ->where('u.username', 'like', '%'.$this->username.'%')
            // ->whereRaw('MATCH(u.username) AGAINST(? IN BOOLEAN MODE)', [$this->username])
            ->when($this->page_no, function ($condition) use ($offset) {
                return $condition->limit(Config::get('variable_constants.pagination.search_users'))->offset($offset);
            })
            ->groupBy('u.id')
            ->get();
    }

    /**
     * Friends and non friend both list needed
     */
    public function searchUserByPhoneNumber($phone_numbers): Collection
    {
        $filePath = FunctionHelper::asset_base_url(Config::get('path_constants.user_avatar'));
        $this->page_no = max($this->page_no, 1);
        $offset = ($this->page_no - 1) * Config::get('variable_constants.pagination.search_users');

        return DB::table('users as u')
            ->select('u.id as user_id', 'u.username', 'u.is_active', 'u.phone_number',
                DB::raw('IFNULL(CONCAT("'.$filePath.'" , u.avatar), null) as avatar'),
                DB::raw('IF(COUNT(f.friend_id) > 0, 1, 0) as is_friend'))
            ->leftJoin('friends as f', function ($query) {
                $query->on('u.id', '=', 'f.friend_id')
                    ->where('f.user_id', '=', $this->id)
                    ->whereNull('f.deleted_at')
                    ->where('f.status', '=', Config::get('variable_constants.activation.active'));

            })
            ->where('u.id', '!=', $this->id)
            ->where('u.status', '=', Config::get('variable_constants.activation.active'))
            ->whereNull('u.deleted_at')
            ->whereIn(DB::raw('REGEXP_REPLACE(u.phone_number, "[^0-9]", "")'), $phone_numbers)
            ->when($this->page_no, function ($condition) use ($offset) {
                return $condition->limit(Config::get('variable_constants.pagination.search_users'))->offset($offset);
            })
            ->groupBy('u.id')
            ->get();
    }

    public function getUserProfileInfo()
    {
        $outfitFilePath = FunctionHelper::asset_base_url(Config::get('path_constants.user_outfits'));
        $avatarFilePath = FunctionHelper::asset_base_url(Config::get('path_constants.user_avatar'));

        return DB::table('users as u')
            ->select('u.id', 'u.device_token', 'u.username', 'u.socket_id', DB::raw('DATE_FORMAT(u.dob, "%d/%m/%Y") as dob'), 'u.phone_number',
                'u.is_registration_complete', 'u.is_guest_user', 'u.is_premium', 'u.is_active', DB::raw('IFNULL(CONCAT("'.$avatarFilePath.'" , u.avatar), null) as avatar'),
                DB::raw('DATE_FORMAT(u.created_at, "%d/%m/%Y %H:%i:%s") as created_at'), DB::raw('DATE_FORMAT(u.updated_at, "%d/%m/%Y %H:%i:%s") as updated_at'), DB::raw('DATE_FORMAT(u.username_updated_at, "%d/%m/%Y %H:%i:%s") as username_updated_at'),
                'uo.model as outfit_model', DB::raw('IFNULL(CONCAT("'.$outfitFilePath.'" , uo.image), null) as outfit_image'),
                DB::raw('(SELECT COUNT(*) FROM private_channels as pc WHERE pc.user_id = u.id AND pc.deleted_at IS NULL) as owned_channels'),
                DB::raw('(SELECT COUNT(*) FROM favourite_channels as fc WHERE fc.user_id = u.id) as favourite_channels'),
                DB::raw('(SELECT COUNT(*) FROM private_channel_members as pcm
                    JOIN private_channels as pc ON pcm.private_channel_id = pc.id
                    WHERE pcm.user_id = u.id AND pcm.user_id != pc.user_id AND pcm.deleted_at IS NULL AND pcm.status = 1 AND pc.deleted_at IS NULL) as joined_channels')
            )
            ->leftJoin('user_outfits as uo', 'uo.user_id', '=', 'u.id')
            ->where('u.id', '=', $this->id)
            ->first();
    }

    public function updateUserOutfit()
    {
        $outfitExists = DB::table('user_outfits')->where('user_id', '=', $this->id)->first();
        DB::beginTransaction();
        DB::table('users')->where('id', '=', $this->id)->update([
            'avatar' => $this->avatar,
            'updated_at' => Carbon::now(),
        ]);
        if ($outfitExists) {
            $userOutfit = DB::table('user_outfits')->where('user_id', '=', $this->id)->update([
                'model' => $this->outfit_model,
                'image' => $this->outfit_image,
                'updated_at' => Carbon::now(),
            ]);
        } else {
            $userOutfit = DB::table('user_outfits')->insert([
                'user_id' => $this->id,
                'model' => $this->outfit_model,
                'image' => $this->outfit_image,
                'created_at' => Carbon::now(),
            ]);
        }
        DB::commit();

        return $userOutfit;

    }
}
