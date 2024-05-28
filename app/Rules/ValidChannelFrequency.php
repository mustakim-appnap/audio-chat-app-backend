<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class ValidChannelFrequency implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $public = DB::table('public_channels')->where('frequency', $value)->exists();
        $private = DB::table('private_channels')->where('frequency', $value)->exists();

        $public || $private ? true : $fail("Channel doesn't exists");
    }
}
