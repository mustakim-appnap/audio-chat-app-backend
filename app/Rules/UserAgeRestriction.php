<?php

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UserAgeRestriction implements ValidationRule
{
    protected $minAge;

    public function __construct($minAge)
    {
        $this->minAge = $minAge;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $dob = Carbon::createFromFormat('d/m/Y', $value);
        $minDate = now()->subYears($this->minAge);
        if (! $dob->lte($minDate)) {
            $fail('User must be at least 13 years old.');
        }
    }
}
