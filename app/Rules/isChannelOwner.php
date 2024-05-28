<?php

namespace App\Rules;

use App\Enums\Tables;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class isChannelOwner implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        $channelOwner = DB::table(Tables::PRIVATE_CHANNELS)->where('id', $value)->where('user_id', Auth::id())->first();

        if (! $channelOwner) {
            $fail('User not authorized to kick member');
        }
    }
}
