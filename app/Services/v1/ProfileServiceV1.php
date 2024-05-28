<?php

namespace App\Services\v1;

use App\Contracts\FileUploadContract;
use App\Helpers\DateHelper;
use App\Repositories\v1\AuthRepositoryV1;
use App\Repositories\v1\FriendRequestRepositoryV1;
use App\Repositories\v1\ProfileRepositoryV1;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use stdClass;

class ProfileServiceV1
{
    public function __construct(protected ProfileRepositoryV1 $profileRepositoryV1, protected AuthRepositoryV1 $authRepositoryV1, protected FriendRequestRepositoryV1 $friendRequestRepositoryV1, protected FileUploadContract $fileUploadContract)
    {

    }

    public function checkUsername($username)
    {
        return $this->profileRepositoryV1->setUsername($username)->checkUsernameExists();
    }

    public function updateBasicInfo($data)
    {
        return $this->profileRepositoryV1->setId(Auth::id())->setUsername($data['username'])->setDob(DateHelper::format_date($data['dob']))->updateBasicInfo();
    }

    public function updateUsername($data)
    {
        return $this->profileRepositoryV1->setId(Auth::id())
            ->setUsername($data['username'])
            ->updateUsername();
    }

    public function searchUserByUsername($username)
    {
        $users = $this->profileRepositoryV1->setId(Auth::id())->setUsername($username)->setPage(request()->get('page'))->getUsersByUsername();

        foreach ($users as $key => $user) {
            $friendRequest = $user->is_friend == Config::get('variable_constants.check.no') ? $this->friendRequestRepositoryV1->setSenderId(Auth::id())->setReceiverId($user->user_id)->getRequestStatus() : null;
            $user->is_request_sender = $friendRequest ? $friendRequest->is_request_sender : 0;
            $user->request_status = $friendRequest ? $friendRequest->request_status : 0;
            $user->request_id = $friendRequest ? $friendRequest->request_id : null;
        }

        return $users;
    }

    public function searchUserByPhoneNumber($phone_numbers): array
    {
        $phoneNumbers = array_map(function ($number) {
            return preg_replace('/[^0-9]/', '', $number);
        }, $phone_numbers);
        $users = $this->profileRepositoryV1->setId(Auth::id())->setPage(request()->get('page'))->searchUserByPhoneNumber($phoneNumbers);
        $response = [
            'friends' => [],
            'not_friends' => [],
        ];

        foreach ($users as $key => $user) {
            if ($user->is_friend) {
                $response['friends'][] = $user;
            } else {
                $response['not_friends'][] = $user;
            }
        }

        return $response;
    }

    public function deleteAccount()
    {
        $this->authRepositoryV1->setId(Auth::id())->deleteAllTokens();

        return $this->profileRepositoryV1->setId(Auth::id())->delete();
    }

    public function getUserProfileInfo()
    {
        $userInfo = $this->profileRepositoryV1->setId(Auth::id())->getUserProfileInfo();
        if ($userInfo) {
            $userInfo->outfits = new stdClass();
            $userInfo->outfits->model = ($userInfo->outfit_model);
            $userInfo->outfits->image = $userInfo->outfit_image;
            unset($userInfo->outfit_model);
            unset($userInfo->outfit_image);
        }

        return $userInfo;
    }

    public function updateUserOutfit($data)
    {
        $this->fileUploadContract->setPath(Config::get('path_constants.user_avatar'));
        $avatar = $this->fileUploadContract->upload_base64_file($data['avatar']);
        $this->profileRepositoryV1->setId(Auth::id())->setAvatar($avatar['file_name'])->setOutfitModel(($data['model']))->updateUserOutfit();

        return $this->getUserProfileInfo();
    }
}
