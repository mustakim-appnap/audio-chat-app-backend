<?php

namespace App\Repositories\v1;

use App\Models\ReportType;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class ReportRepositoryV1
{
    private $reporter_id;

    private $reported_user_id;

    private $created_at;

    private $report_type_id;

    public function setReporterId($reporter_id)
    {
        $this->reporter_id = $reporter_id;

        return $this;
    }

    public function setReportedUserId($reported_user_id)
    {
        $this->reported_user_id = $reported_user_id;

        return $this;
    }

    public function setReportTypeId($report_type_id)
    {
        $this->report_type_id = $report_type_id;

        return $this;
    }

    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getReportTypes()
    {
        return Cache::rememberForever('reportTypes', function () {
            return ReportType::where('status', Config::get('variable_constants.activation.active'))->get(['id', 'title']);
        });

    }

    public function reportUser()
    {
        DB::beginTransaction();
        $reportId = DB::table('user_reports')
            ->insertGetId([
                'reporter_id' => $this->reporter_id,
                'reported_user_id' => $this->reported_user_id,
                'created_at' => $this->created_at,
            ]);
        if ($reportId) {
            DB::table('user_report_types')->insert([
                'user_report_id' => $reportId,
                'report_type_id' => $this->report_type_id,
                'created_at' => $this->created_at,
            ]);
        }
        DB::commit();

        return $reportId;
    }
}
