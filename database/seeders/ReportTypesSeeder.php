<?php

namespace Database\Seeders;

use App\Models\ReportType;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReportTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reportTypes = [
            [
                'id' => 1,
                'title' => 'Annoying',
                'created_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'title' => 'Harassment',
                'created_at' => Carbon::now(),
            ],
            [
                'id' => 3,
                'title' => 'Sexual Harassment',
                'created_at' => Carbon::now(),
            ],
            [
                'id' => 4,
                'title' => 'Violance',
                'created_at' => Carbon::now(),
            ],
            [
                'id' => 5,
                'title' => 'Scam',
                'created_at' => Carbon::now(),
            ],
            [
                'id' => 6,
                'title' => 'Self-Injury',
                'created_at' => Carbon::now(),
            ],
            [
                'id' => 7,
                'title' => 'Hate Speech',
                'created_at' => Carbon::now(),
            ],
            [
                'id' => 8,
                'title' => 'Spam',
                'created_at' => Carbon::now(),
            ],
        ];

        foreach ($reportTypes as $key => $report) {
            ReportType::updateOrCreate(['id' => $report['id']], $report);
        }
    }
}
