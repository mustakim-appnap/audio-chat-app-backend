<?php

namespace App\Repositories\v1;

use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Token;

class AuthRepositoryV1
{
    private $auth_id;

    private $device_token;

    private $is_guest_user;

    private $is_registration_complete;

    private $password;

    private $status;

    private $last_login;

    private $created_at;

    private $id;

    private $username;

    private $dob;

    private $avatar;

    private $username_updated_at;

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function setDeviceToken($device_token)
    {
        $this->device_token = $device_token;

        return $this;
    }

    public function setAuthId($auth_id)
    {
        $this->auth_id = $auth_id;

        return $this;
    }

    public function setIsGuestUser($is_guest_user)
    {
        $this->is_guest_user = $is_guest_user;

        return $this;
    }

    public function setIsRegistrationComplete($is_registration_complete)
    {
        $this->is_registration_complete = $is_registration_complete;

        return $this;
    }

    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    public function setLastLogin($last_login)
    {
        $this->last_login = $last_login;

        return $this;
    }

    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    public function setDob($dob)
    {
        $this->dob = $dob;

        return $this;
    }

    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function setUsernameUpdatedAt($username_updated_at)
    {
        $this->username_updated_at = $username_updated_at;

        return $this;
    }

    public function getUserByDeviceToken()
    {
        return DB::table('users as u')
            ->whereNull('u.deleted_at')
            ->where('u.device_token', '=', $this->device_token)
            ->select('u.id', 'u.device_token', 'u.username', 'u.socket_id', DB::raw('DATE_FORMAT(u.dob, "%d/%m/%Y") as dob'), 'u.phone_number',
                'u.is_registration_complete', 'u.is_guest_user', 'u.is_premium', 'u.is_active', 'u.created_at')
            ->first();
    }

    public function getUserByDeviceTokenWithoutAuthId()
    {
        return DB::table('users as u')
            ->whereNull('u.deleted_at')
            ->where('u.device_token', '=', $this->device_token)
            ->whereNull('u.auth_id')
            ->select('u.id', 'u.device_token', 'u.username', 'u.socket_id', DB::raw('DATE_FORMAT(u.dob, "%d/%m/%Y") as dob'), 'u.phone_number',
                'u.is_registration_complete', 'u.is_guest_user', 'u.is_premium', 'u.is_active', 'u.created_at')
            ->first();
    }

    public function getUserByAuthId()
    {
        return DB::table('users as u')
            ->whereNull('u.deleted_at')
            ->where('u.auth_id', '=', $this->auth_id)
            ->select('u.id', 'u.auth_id', 'u.device_token', 'u.username', 'u.socket_id', DB::raw('DATE_FORMAT(u.dob, "%d/%m/%Y") as dob'), 'u.phone_number',
                'u.is_registration_complete', 'u.is_guest_user', 'u.is_premium', 'u.is_active', 'u.created_at')
            ->first();
    }

    public function store()
    {
        return DB::table('users')
            ->insertGetId([
                'auth_id' => $this->auth_id,
                'device_token' => $this->device_token,
                'username' => $this->username,
                'dob' => $this->dob,
                'status' => $this->status,
                'is_guest_user' => $this->is_guest_user,
                'is_registration_complete' => $this->is_registration_complete,
                'created_at' => $this->created_at,
                'avatar' => Config::get('variable_constants.default.user_avatar'),
                'push_notification_status' => Config::get('variable_constants.check.yes'),
                'username_updated_at' => $this->username_updated_at,
            ]);

    }

    public function setUserOutfits($userId)
    {
        $outfitIndex = array_rand(Config::get('outfits.default'));
        $outfitModel = Config::get('outfits.default.'.$outfitIndex.'.model');
        $outfitImage = Config::get('outfits.default.'.$outfitIndex.'.image');

        return DB::table('user_outfits')->insert([
            'user_id' => $userId,
            'model' => $outfitModel,
            'image' => $outfitImage,
            'created_at' => Carbon::now(),

        ]);
    }

    public function getUserInfo()
    {
        return DB::table('users as u')
            ->when($this->id, function ($condition) {
                return $condition->where('u.id', '=', $this->id);
            })
            ->where('u.auth_id', '=', $this->auth_id)
            ->where('u.device_token', '=', $this->device_token)
            ->whereNull('u.deleted_at')
            ->select('u.id', 'u.device_token', 'u.username', 'u.socket_id', DB::raw('DATE_FORMAT(u.dob, "%d/%m/%Y") as dob'), 'u.phone_number',
                'u.is_registration_complete', 'u.is_guest_user', 'u.is_premium', 'u.is_active', DB::raw('DATE_FORMAT(u.created_at, "%d/%m/%Y %H:%i:%s") as created_at'))
            ->first();
    }

    public function getUserCollection()
    {
        return User::find($this->id);
    }

    public function revokeAccessTokens()
    {
        DB::beginTransaction();
        $access_tokens = DB::table('oauth_access_tokens')
            ->where('user_id', '=', $this->id)
            ->where('revoked', '=', Config::get('variable_constants.check.no'))
            ->get();
        if ($access_tokens) {
            foreach ($access_tokens as $access_token) {
                DB::table('oauth_access_tokens')
                    ->where('id', '=', $access_token->id)
                    ->update([
                        'revoked' => Config::get('variable_constants.check.yes'),
                    ]);
            }
        }
        DB::table('users')->where('id', '=', $this->id)->update([
            'socket_id' => null,
            'is_active' => Config::get('variable_constants.check.no'),
        ]);
        DB::commit();

        return true;
    }

    public function getGuestUser()
    {
        return DB::table('users as u')
            ->whereNull('u.deleted_at')
            ->whereNull('u.auth_id')
            ->where('u.device_token', '=', $this->device_token)
            ->select('u.id', 'u.device_token', 'u.username', 'u.socket_id', DB::raw('DATE_FORMAT(u.dob, "%d/%m/%Y") as dob'), 'u.phone_number',
                'u.is_registration_complete', 'u.is_guest_user', 'u.is_premium', 'u.is_active', DB::raw('DATE_FORMAT(u.created_at, "%d/%m/%Y %H:%i:%s") as created_at'))
            ->first();
    }

    public function updateAuthId()
    {
        return DB::table('users')
            ->where('id', '=', $this->id)
            ->update([
                'auth_id' => $this->auth_id,
                'is_guest_user' => Config::get('variable_constants.check.no'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    public function saveDeviceToken()
    {
        return DB::table('device_tokens')
            ->insert([
                'user_id' => $this->id,
                'device_token' => $this->device_token,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
    }

    public function getUserLastActivity()
    {
        return DB::table('api_logs as log')
            ->whereNull('log.deleted_at')
            ->where('log.user_id', '=', $this->id)
            ->orderBy(DB::raw('DATE(log.created_at)'), 'desc')
            ->select(DB::raw('DATE_FORMAT(log.created_at, "%d/%m/%Y %H:%i:%s") as created_at'))
            ->first();
    }

    public function deleteAllTokens()
    {
        try {
            DB::beginTransaction();
            // Delete refresh tokens
            DB::table('oauth_refresh_tokens')
                ->whereIn('access_token_id', function ($query) {
                    $query->select('id')->from('oauth_access_tokens')->where('user_id', $this->id);
                })->delete();

            Token::where('user_id', $this->id)->delete();

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();

            return $e->getMessage();
        }

    }
}
