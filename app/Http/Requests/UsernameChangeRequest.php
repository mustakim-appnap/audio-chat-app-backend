<?php

namespace App\Http\Requests;

use App\Rules\CanChangeUsernameRule;
use App\Rules\OffensiveUsernameRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UsernameChangeRequest extends FormRequest
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
                'min:3',
                'max:18',
                'regex:/^[a-zA-Z0-9._]+$/',
                'required',
                'unique:users,username,"'.Auth::id().'"',
                new OffensiveUsernameRule(),
                new CanChangeUsernameRule(),
            ],
        ];
    }
}
