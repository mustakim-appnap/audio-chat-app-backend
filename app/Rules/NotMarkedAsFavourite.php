<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotMarkedAsFavourite implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $data = DB::table('favourite_channels')->where('user_id', Auth::id())->where('channel_frequency', $value)->exists();
        if ($data) {
            $fail('The channel is already marked as favourite');
        }

    }
}
