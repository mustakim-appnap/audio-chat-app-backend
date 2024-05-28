<?php

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class CanChangeUsernameRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = DB::table('users')->where('id', Auth::id())->select('username_updated_at')->first();
        $updatedAt = Carbon::parse($user->username_updated_at);
        $today = Carbon::today();
        $difference = $updatedAt->diffInDays($today);
        if ($user->username_updated_at && $difference < Config::get('variable_constants.default.username_change_interval_in_days')) {
            $reamining_days = Config::get('variable_constants.default.username_change_interval_in_days') - $difference;
            // $fail("you can't change username in ".$reamining_days.' days');
            $fail('Your can only change username once in every 30 days');
        }
    }
}
