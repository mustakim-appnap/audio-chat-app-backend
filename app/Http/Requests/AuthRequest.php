<?php

namespace App\Http\Requests;

use App\Rules\OffensiveUsernameRule;
use App\Rules\UserAgeRestriction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AuthRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'auth_id' => 'nullable',
            'device_token' => 'required',
            'username' => [
                'min:3',
                'max:18',
                'regex:/^[a-zA-Z0-9._]+$/',
                'required_if:auth_id,value',
                Rule::requiredIf(function () {
                    $userNotExists = DB::table('users')
                        ->where(function ($query) {
                            $query->where('auth_id', request()->input('auth_id'))
                                ->orWhere('device_token', request()->input('device_token'));
                        })->doesntExist();

                    return $userNotExists;
                }),
                new OffensiveUsernameRule(),
            ],
            'dob' => [
                'nullable',
                'date_format:d/m/Y',
                new UserAgeRestriction(Config::get('variable_constants.age_restriction')),
                Rule::requiredIf(function () {
                    $userNotExists = DB::table('users')
                        ->where(function ($query) {
                            $query->where('auth_id', request()->input('auth_id'))
                                ->orWhere('device_token', request()->input('device_token'));
                        })->doesntExist();

                    return $userNotExists;
                }),
            ],
            'data_to_be_deleted' => 'nullable|integer|min:0|max:1',
            'user_id' => 'nullable',
        ];
    }
}
