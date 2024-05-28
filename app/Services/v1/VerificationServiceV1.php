<?php

namespace App\Services\v1;

use App\Repositories\v1\VerificationRepositoryV1;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class VerificationServiceV1
{
    public function __construct(protected VerificationRepositoryV1 $verificationRepositoryV1)
    {

    }

    public function sendOtp($phone)
    {
        $otp = 123456;
        $this->verificationRepositoryV1->setPhoneNumber($phone);
        $this->verificationRepositoryV1->setOtp($otp);
        $this->verificationRepositoryV1->setExpiration(date('Y-m-d H:i:s', time() + (Config::get('variable_constants.otp_expiration_time'))));
        $code = $this->verificationRepositoryV1->saveOtp();
        if ($code) {
            return true;
        }

        return false;
    }

    public function verifyOtp($phone_number, $otp)
    {
        $this->verificationRepositoryV1->setPhoneNumber($phone_number);
        $this->verificationRepositoryV1->setOtp($otp);
        $this->verificationRepositoryV1->setUserId(Auth::id());
        $response = $this->verificationRepositoryV1->verifyOtp();
        if ($response) {
            if (strtotime($response->expires_at) >= time()) {
                $this->verificationRepositoryV1->setVerificationCodeId($response->id);
                $this->verificationRepositoryV1->makeOtpVerified();
                $this->verificationRepositoryV1->setUserId(Auth::id());

                return $this->verificationRepositoryV1->updateUserPhoneNumber();
            } else {
                return json_encode([
                    'otp' => [
                        'Otp expired!',
                    ],
                ]);
            }
        } else {
            return json_encode([
                'otp' => [
                    'Invalid OTP, Please try with correct OTP!',
                ],
            ]);
        }
    }
}
