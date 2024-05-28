<?php

namespace App\Services\v1;

use App\Repositories\v1\ReportRepositoryV1;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReportServiceV1
{
    public function __construct(protected ReportRepositoryV1 $reportRepositoryV1)
    {

    }

    public function reportTypes()
    {
        return $this->reportRepositoryV1->getReportTypes();

    }

    public function reportUser($data)
    {
        return $this->reportRepositoryV1->setReporterId(Auth::id())
            ->setReportedUserId($data['user_id'])
            ->setReportTypeId($data['report_id'])
            ->setCreatedAt(Carbon::now())
            ->reportUser();
    }
}
