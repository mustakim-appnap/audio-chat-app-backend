<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePushNotificationStatusRequest extends FormRequest
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
            'is_allowed' => 'boolean|required',
            'channel_invitation' => 'boolean|required',
            'friend_request' => 'boolean|required',
            'message' => 'boolean|required',
            'promotional' => 'boolean|required',
        ];
    }
}
