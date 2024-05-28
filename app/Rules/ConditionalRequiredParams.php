<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class ConditionalRequiredParams implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        DB::table('users')
            ->where(function ($query) {
                $query->where('auth_id', request()->input('auth_id'))
                    ->orWhere('device_token', request()->input('device_token'));
            })->doesntExist();
    }
}
