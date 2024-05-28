<?php

namespace Database\Seeders;

use App\Models\PublicChannel;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PublicChannelCreationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $channels = [];

        // Loop for integer part (00 to 99)
        for ($i = 0; $i <= 99; $i++) {
            // Loop for decimal part (00 to 99)
            for ($j = 0; $j <= 99; $j++) {
                // Format the numbers with leading zeros
                $integerPart = str_pad($i, 2, '0', STR_PAD_LEFT);
                $decimalPart = str_pad($j, 2, '0', STR_PAD_LEFT);

                // Concatenate integer and decimal parts with a dot
                $channel = $integerPart.'.'.$decimalPart;

                // Add the number to the array
                $channels[] = $channel;
            }
        }

        foreach ($channels as $key => $channel) {
            $publicChannel = [
                'id' => $key + 1,
                'frequency' => $channel,
                'status' => 1,
                'created_at' => Carbon::now(),
            ];

            PublicChannel::updateOrCreate(['id' => $publicChannel['id']], $publicChannel);
        }
    }
}
