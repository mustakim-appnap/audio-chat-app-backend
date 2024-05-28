<?php

namespace Database\Seeders;

use App\Models\SoundCategory;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SoundCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'id' => 1,
                'name' => 'Basic',
                'created_at' => Carbon::now(),
            ],

            [
                'id' => 2,
                'name' => 'Games',
                'created_at' => Carbon::now(),
            ],
            [
                'id' => 3,
                'name' => 'SFX',
                'created_at' => Carbon::now(),
            ],
            [
                'id' => 4,
                'name' => 'Pop Culture',
                'created_at' => Carbon::now(),
            ],
            [
                'id' => 5,
                'name' => 'Meme',
                'created_at' => Carbon::now(),
            ],

        ];

        foreach ($categories as $key => $category) {
            SoundCategory::updateOrCreate(['id' => $category['id']], $category);
        }

    }
}
