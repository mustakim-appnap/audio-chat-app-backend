<?php

namespace App\Repositories\v1;

use App\Helpers\FunctionHelper;
use App\Models\SoundCategory;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SoundRepositoryV1
{
    public function getAllSounds()
    {
        $filePath = FunctionHelper::asset_base_url(Config::get('path_constants.sounds'));

        return SoundCategory::with(['sounds' => function ($q) use ($filePath) {
            $q->where('status', Config::get('variable_constants.activation.active'));
            $q->select('id as sound_id', 'sound_category_id', 'title', DB::raw('CONCAT("'.$filePath.'" , image) as image'), DB::raw('CONCAT("'.$filePath.'" , file_name) as audio'));
        }])->where('status', Config::get('variable_constants.activation.active'))->get(['id', 'name']);
    }
}
