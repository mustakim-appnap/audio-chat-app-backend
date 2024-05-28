<?php

namespace App\Http\Controllers\v1\Report;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserReportRequest;
use App\Services\v1\ReportServiceV1;
use Illuminate\Http\Response;

class ReportControllerV1 extends Controller
{
    public function __construct(protected ReportServiceV1 $reportServiceV1)
    {
    }

    public function index()
    {
        try {
            $response = $this->reportServiceV1->reportTypes();
            if ($response) {
                return response(['success' => true, 'error' => null, 'data' => $response], Response::HTTP_OK);
            } else {
                return response(['success' => false, 'error' => $response], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function reportUser(UserReportRequest $request)
    {
        try {
            $response = $this->reportServiceV1->reportUser($request->validated());
            if ($response) {
                return response(['success' => true, 'error' => null], Response::HTTP_OK);
            } else {
                return response(['success' => false, 'error' => $response], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
