<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;

class CommonControllerV1 extends Controller
{
    public function offensiveWords()
    {
        try {
            $data = Config::get('validation.offensive_words');

            return response(['success' => true, 'error' => null, 'data' => $data], Response::HTTP_OK);
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function appSettings()
    {
        try {
            $data = AppSetting::select('version', 'show_review_controller', 'app_url', 'force_update')->first();

            return response(['success' => true, 'error' => null, 'data' => $data], Response::HTTP_OK);
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
