<?php

namespace App\Repositories\v1;

use App\Models\PhoneVerificationCode;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class VerificationRepositoryV1
{
    private $otp;

    private $phone_number;

    private $expiresAt;

    private $verificationCodeId;

    private $userId;

    public function setPhoneNumber($phone_number)
    {
        $this->phone_number = $phone_number;
    }

    public function setOtp($otp)
    {
        $this->otp = $otp;
    }

    public function setVerificationCodeId($verificationCodeId)
    {
        $this->verificationCodeId = $verificationCodeId;
    }

    public function setExpiration($expiresAt)
    {
        $this->expiresAt = $expiresAt;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function saveOtp()
    {
        DB::beginTransaction();
        PhoneVerificationCode::where('phone_number', $this->phone_number)
            ->where('is_verified', Config::get('variable_constants.check.no'))
            ->update([
                'is_expired' => Config::get('variable_constants.check.yes'),
                'updated_at' => Carbon::now(),
            ]);
        $otp = new PhoneVerificationCode();
        $otp->phone_number = $this->phone_number;
        $otp->code = $this->otp;
        $otp->expires_at = $this->expiresAt;
        $otp->created_at = Carbon::now();
        $otp->save();
        DB::commit();

        return $otp->code;
    }

    public function makeOtpVerified()
    {
        $code = PhoneVerificationCode::find($this->verificationCodeId);
        $code->is_verified = Config::get('variable_constants.check.yes');
        $code->updated_at = Carbon::now();

        return $code->save();

    }

    public function verifyOtp()
    {
        return PhoneVerificationCode::where('phone_number', $this->phone_number)
            ->where('code', $this->otp)
            ->where('is_verified', Config::get('variable_constants.check.no'))
            ->where('is_expired', Config::get('variable_constants.check.no'))
            ->first();
    }

    public function updateUserPhoneNumber()
    {
        return User::where('id', '=', $this->userId)->update([
            'phone_number' => $this->phone_number,
            'updated_at' => Carbon::now(),
        ]);
    }
}
