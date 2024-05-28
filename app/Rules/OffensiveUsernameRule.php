<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Config;

class OffensiveUsernameRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $offensiveWords = Config::get('validation.offensive_words');

        foreach ($offensiveWords as $word) {
            if (stripos($value, $word) !== false) {
                $fail('offensive username will be sanctioned');
            }
        }
    }
}
