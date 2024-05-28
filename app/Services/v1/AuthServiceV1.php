<?php

namespace App\Services\v1;

use App\Helpers\DateHelper;
use App\Repositories\v1\AuthRepositoryV1;
use App\Repositories\v1\ProfileRepositoryV1;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Client as OClient;

class AuthServiceV1
{
    private $authRepositoryV1;

    private $profileRepositoryV1;

    public function __construct(AuthRepositoryV1 $authRepositoryV1, ProfileRepositoryV1 $profileRepositoryV1)
    {
        $this->authRepositoryV1 = $authRepositoryV1;
        $this->profileRepositoryV1 = $profileRepositoryV1;
    }

    public function authenticate($data)
    {
        if (isset($data['auth_id']) && ! empty($data['auth_id'])) {
            return $this->authenticateAppleId($data);
        } else {
            return $this->authenticateGuest($data);
        }
    }

    public function checkAccounts($data)
    {
        $auth_id = $data['auth_id'];
        $device_token = $data['device_token'];
        $this->authRepositoryV1->setAuthId($auth_id);
        $this->authRepositoryV1->setDeviceToken($device_token);
        $guest_user = $this->authRepositoryV1->getGuestUser();
        $apple_id_user = $this->authRepositoryV1->getUserInfo();
        $response = [];
        if ($apple_id_user) {
            $this->authRepositoryV1->setId($apple_id_user->id);
            $apple_user_last_activity = $this->authRepositoryV1->getUserLastActivity();
            $apple_id_user->last_activity = $apple_user_last_activity ? $apple_user_last_activity->created_at : $apple_id_user->created_at;
            array_push($response, $apple_id_user);
        }
        if ($guest_user) {
            $this->authRepositoryV1->setId($guest_user->id);
            $guest_user_last_activity = $this->authRepositoryV1->getUserLastActivity();
            $guest_user->last_activity = $guest_user_last_activity ? $guest_user_last_activity->created_at : $guest_user->created_at;
            array_push($response, $guest_user);
        }

        return $response;
    }

    public function authenticateAppleId($data)
    {
        $this->authRepositoryV1->setAuthId($data['auth_id']);
        $user = $this->authRepositoryV1->getUserByAuthId();
        $existing_guest_user = $this->authRepositoryV1->setDeviceToken($data['device_token'])
            ->getGuestUser();
        if (! $user) {
            if (! $existing_guest_user) {
                $id = $this->createNewUser($data);
                $this->authRepositoryV1->setId($id);
                $user = $this->authRepositoryV1->getUserCollection();
            } else {
                if ($data['data_to_be_deleted']) {
                    $this->authRepositoryV1->setId($existing_guest_user->id);
                    $this->authRepositoryV1->revokeAccessTokens();

                    $this->profileRepositoryV1->setId($existing_guest_user->id);
                    $this->profileRepositoryV1->delete();

                    $id = $this->createNewUser($data, false);
                    $this->authRepositoryV1->setId($id);
                    $user = $this->authRepositoryV1->getUserCollection();
                } else {
                    $this->authRepositoryV1->setId($existing_guest_user->id)
                        ->setAuthId($data['auth_id'])
                        ->updateAuthId();
                    $user = $this->authRepositoryV1->setId($existing_guest_user->id)->getUserCollection();
                }
            }
        } else {
            if ($existing_guest_user) {
                // dd($user, $existing_guest_user);
                if (! $data['user_id']) {
                    return [
                        'success' => false,
                        'error' => 'Multiple Account Found! Please Select One Account.',
                        'access_token' => null,
                        'refresh_token' => null,
                        'user' => null,
                    ];
                } else {
                    if ($user->id == $data['user_id'] || $existing_guest_user->id == $data['user_id']) {
                        if ($data['user_id'] == $existing_guest_user->id) {
                            $this->authRepositoryV1->setId($user->id);
                            $this->authRepositoryV1->revokeAccessTokens();

                            $this->profileRepositoryV1->setId($user->id);
                            $this->profileRepositoryV1->delete();

                            $this->authRepositoryV1->setId($existing_guest_user->id)
                                ->setAuthId($data['auth_id'])
                                ->updateAuthId();
                        }
                        if ($data['user_id'] == $user->id) {
                            $this->authRepositoryV1->setId($existing_guest_user->id);
                            $this->authRepositoryV1->revokeAccessTokens();

                            $this->profileRepositoryV1->setId($existing_guest_user->id);
                            $this->profileRepositoryV1->delete();
                        }
                        $user = $this->authRepositoryV1->setId($data['user_id'])->getUserCollection();
                    } else {
                        return [
                            'success' => false,
                            'error' => 'Invalid User ID',
                            'access_token' => null,
                            'refresh_token' => null,
                            'user' => null,
                        ];
                    }
                }
            } else {
                $user = $this->authRepositoryV1->setId($user->id)->getUserCollection();
            }
        }
        if (! ($user->device_token == $data['device_token'])) {
            $this->authRepositoryV1->setId($user->id)->setDeviceToken($data['device_token'])->saveDeviceToken();
        }
        $passport = $this->getAccessAndRefreshToken($user);

        return [
            'success' => true,
            'error' => null,
            'access_token' => 'Bearer '.$passport['access_token'],
            'refresh_token' => $passport['refresh_token'],
            'user' => $user,
        ];
    }

    public function authenticateGuest($data)
    {
        $device_token = $data['device_token'];
        $data_to_be_deleted = isset($data['data_to_be_deleted']) ? $data['data_to_be_deleted'] : 0;

        $this->authRepositoryV1->setDeviceToken($device_token);
        $user = $this->authRepositoryV1->getUserByDeviceTokenWithoutAuthId();
        if (! $user) {
            $id = $this->createNewUser($data, true);
            $this->authRepositoryV1->setId($id);
            $user = $this->authRepositoryV1->getUserCollection();
        } else {
            if ($data_to_be_deleted) {
                $this->authRepositoryV1->setId($user->id);
                $this->authRepositoryV1->revokeAccessTokens();

                $this->profileRepositoryV1->setId($user->id);
                $this->profileRepositoryV1->delete();

                $id = $this->createNewUser($data, true);
                $this->authRepositoryV1->setId($id);
                $user = $this->authRepositoryV1->getUserCollection();
            }

            $user = $this->authRepositoryV1->setId($user->id)->getUserCollection();
        }
        $passport = $this->getAccessAndRefreshToken($user);

        return [
            'success' => true,
            'error' => null,
            'access_token' => 'Bearer '.$passport['access_token'],
            'refresh_token' => $passport['refresh_token'],
            'user' => $user,
        ];
    }

    public function createNewUser($data, $is_guest = false)
    {
        $time = date('Y-m-d H:i:s');
        if ($is_guest) {
            $userId = $this->authRepositoryV1->setDeviceToken($data['device_token'])
                ->setPassword(Hash::make('welcome'))
                ->setUsername(Config::get('variable_constants.default.username').'_'.substr(uniqid(), -4))
                ->setDob(isset($data['dob']) ? DateHelper::format_date($data['dob']) : null)
                ->setIsRegistrationComplete(Config::get('variable_constants.check.yes'))
                ->setStatus(Config::get('variable_constants.activation.active'))
                ->setIsGuestUser(true)
                ->setLastLogin($time)
                ->setCreatedAt($time)
                ->setUsernameUpdatedAt(null)
                ->store();
            // Store default outfit after user creation
            $this->authRepositoryV1->setUserOutfits($userId);

            return $userId;
        } else {
            $userId = $this->authRepositoryV1->setDeviceToken($data['device_token'])
                ->setAuthId($data['auth_id'])
                ->setUsername($data['username'])
                ->setDob(DateHelper::format_date($data['dob']))
                ->setPassword(Hash::make('welcome'))
                ->setIsRegistrationComplete(Config::get('variable_constants.check.yes'))
                ->setStatus(Config::get('variable_constants.activation.active'))
                ->setIsGuestUser(false)
                ->setLastLogin($time)
                ->setCreatedAt($time)
                ->setUsernameUpdatedAt($time)
                ->store();
            // Store default outfit after user creation
            $this->authRepositoryV1->setUserOutfits($userId);

            return $userId;
        }
    }

    public function generateHeaderToken()
    {
        $private_key = \Illuminate\Support\Facades\File::get(storage_path(env('JWT_PRIVATE_KEY_FILE')));

        return JWT::encode([
            'issuer' => env('JWT_ISSUER'),
            'exp' => time() + env('JWT_EXP'),
            'audience' => env('JWT_AUDIENCE'),
            'iat' => time(),
        ], $private_key, env('JWT_ALG'), env('JWT_KEY_ID'));
    }

    public function getAccessAndRefreshToken($userInfo)
    {
        $oClient = OClient::where('password_client', Config::get('variable_constants.check.yes'))->first();
        $request = \Illuminate\Http\Request::create('/oauth/token', 'POST', [
            'grant_type' => 'password',
            'client_id' => $oClient->id,
            'client_secret' => $oClient->secret,
            'username' => 'id-'.$userInfo->id,
            'password' => 'welcome',
            'scope' => '*',
            'user' => $userInfo,
        ]);
        $response = app()->handle($request);

        return json_decode((string) $response->getContent(), true);
    }

    public function logout()
    {
        $this->authRepositoryV1->setId(Auth::id());

        return $this->authRepositoryV1->revokeAccessTokens();
    }
}
