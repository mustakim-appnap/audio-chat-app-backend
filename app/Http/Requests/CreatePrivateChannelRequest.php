<?php

namespace App\Http\Requests;

use App\Rules\ChannelFrequencyFormat;
use Illuminate\Foundation\Http\FormRequest;

class CreatePrivateChannelRequest extends FormRequest
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
            'name' => 'required|string|min:3|max:20',
            'frequency' => [
                'required',
                'string',
                'unique:private_channels,frequency',
                new ChannelFrequencyFormat(),

            ],
        ];
    }
}
