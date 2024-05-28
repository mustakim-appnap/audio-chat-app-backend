<?php

namespace App\Http\Controllers\v1\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\BasicInfoRequest;
use App\Http\Requests\CheckUsernameRequest;
use App\Http\Requests\PhoneVerificationRequest;
use App\Http\Requests\SearchUserByPhoneNoRequest;
use App\Http\Requests\UpdateOutfitRequest;
use App\Http\Requests\UsernameChangeRequest;
use App\Http\Requests\UserSearchRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Services\v1\ProfileServiceV1;
use App\Services\v1\VerificationServiceV1;
use Illuminate\Http\Response;

class ProfileController extends Controller
{
    public function __construct(protected VerificationServiceV1 $verificationServiceV1, protected ProfileServiceV1 $profileServiceV1)
    {

    }

    public function index()
    {
        try {
            $response = $this->profileServiceV1->getUserProfileInfo();
            if ($response) {
                return response(['success' => true, 'error' => null, 'data' => $response], Response::HTTP_OK);
            } else {
                return response(['success' => false, 'error' => $response], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function sendOtp(PhoneVerificationRequest $request)
    {
        try {
            $data = $request->validated();
            $response = $this->verificationServiceV1->sendOtp($data['phone_number']);
            if ($response === true) {
                return response(['success' => true, 'error' => null], Response::HTTP_OK);
            } else {
                return response(['success' => false, 'error' => $response], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function verifyOtp(VerifyOtpRequest $request)
    {
        try {
            $response = $this->verificationServiceV1->verifyOtp($request->phone_number, $request->otp);
            if (is_int($response)) {
                return response(['success' => true, 'error' => null], Response::HTTP_OK);
            } else {
                return response(['message' => 'The given data was invalid', 'errors' => json_decode($response)], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function checkUsername(CheckUsernameRequest $request)
    {
        try {
            $data = $request->validated();
            $response = $this->profileServiceV1->checkUsername($data['username']);
            if (! $response) {
                return response(['success' => true, 'error' => null], Response::HTTP_OK);
            } else {
                return response(['success' => false, 'error' => $response], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateBasicInfo(BasicInfoRequest $request)
    {
        try {
            $data = $request->validated();
            $response = $this->profileServiceV1->updateBasicInfo($data);
            if (is_int($response)) {
                return response(['success' => true, 'error' => null], Response::HTTP_OK);
            } else {
                return response(['message' => 'The given data was invalid', 'errors' => json_decode($response)], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateUserName(UsernameChangeRequest $request)
    {
        try {
            $response = $this->profileServiceV1->updateUsername($request->validated());
            if (is_int($response)) {
                return response(['success' => true, 'error' => null], Response::HTTP_OK);
            } else {
                return response(['message' => 'The given data was invalid', 'errors' => json_decode($response)], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function searchUser(UserSearchRequest $request)
    {
        try {
            $data = $request->validated();
            $response = $this->profileServiceV1->searchUserByUsername($data['username']);
            if ($response) {
                return response(['success' => true, 'error' => null, 'data' => $response], Response::HTTP_OK);
            } else {
                return response(['success' => false, 'error' => $response], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function fetchUserByPhoneNo(SearchUserByPhoneNoRequest $request)
    {
        try {
            $data = $request->validated();
            $response = $this->profileServiceV1->searchUserByPhoneNumber($data['phone_numbers']);
            if ($response) {
                return response(['success' => true, 'error' => null, 'data' => $response], Response::HTTP_OK);
            } else {
                return response(['success' => false, 'error' => $response], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deleteAccount()
    {
        try {
            $response = $this->profileServiceV1->deleteAccount();
            if (is_int($response)) {
                return response(['success' => true, 'error' => null], Response::HTTP_OK);
            } else {
                return response(['success' => false, 'error' => $response], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateUserOutfit(UpdateOutfitRequest $request)
    {
        try {
            $response = $this->profileServiceV1->updateUserOutfit($request->validated());
            if ($response) {
                return response(['success' => true, 'error' => null, 'data' => $response], Response::HTTP_OK);
            } else {
                return response(['success' => false, 'error' => $response], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
