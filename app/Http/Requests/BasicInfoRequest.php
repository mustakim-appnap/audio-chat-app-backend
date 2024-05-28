<?php

namespace App\Http\Requests;

use App\Rules\UserAgeRestriction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;

class BasicInfoRequest extends FormRequest
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
            'username' => [
                'required',
                'min:3',
                'max:18',
                'unique:users,username',
                'regex:/^[a-zA-Z0-9._]+$/',
            ],
            'dob' => [
                'required',
                'date_format:d/m/Y',
                new UserAgeRestriction(Config::get('variable_constants.age_restriction')),
            ],
        ];
    }

    public function messages()
    {
        return [
            'username.required' => 'The username field is required.',
            'username.min' => 'Username must be between :min and 18 characters',
            'username.max' => 'Username must be between 3 and :max characters',
            'username.unique' => 'Not Available',
            'username.regex' => 'Only letters, numbers, dots and underscores are allowed',
        ];
    }
}
