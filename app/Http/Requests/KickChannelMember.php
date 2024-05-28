<?php

namespace App\Http\Requests;

use App\Rules\isChannelOwner;
use Illuminate\Foundation\Http\FormRequest;

class KickChannelMember extends FormRequest
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
            'channel_id' => [
                'required',
                'numeric',
                'exists:private_channels,id',
                new isChannelOwner(),
            ],
            'user_id' => [
                'required',
                'numeric',
            ],
        ];
    }
}