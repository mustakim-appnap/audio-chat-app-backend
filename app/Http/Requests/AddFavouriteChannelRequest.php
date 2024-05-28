<?php

namespace App\Http\Requests;

use App\Rules\NotMarkedAsFavourite;
use App\Rules\ValidChannelFrequency;
use Illuminate\Foundation\Http\FormRequest;

class AddFavouriteChannelRequest extends FormRequest
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
            'channel' => [
                'string',
                'required',
                new NotMarkedAsFavourite(),
                new ValidChannelFrequency(),
            ],
            'channel_type' => 'boolean|required',
        ];
    }
}
