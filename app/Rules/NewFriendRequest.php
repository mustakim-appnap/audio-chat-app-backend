<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class NewFriendRequest implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $requestExists = DB::table('friend_requests')
            ->where('sender_id', Auth::id())
            ->where('receiver_id', $value)
            ->where('status', Config::get('variable_constants.friend_request_status.pending'))
            ->whereNull('deleted_at')
            ->exists();
        if ($requestExists) {
            $fail('Friend request already sent');
        }

        if (Auth::id() == $value) {
            $fail('Invalid friend request');
        }
    }
}
