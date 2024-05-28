<?php

namespace App\Http\Controllers\v1\Asset;

use App\Contracts\FileUploadContract;
use App\Http\Controllers\Controller;
use App\Services\v1\SoundServiceV1;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SoundControllerV1 extends Controller
{
    public function __construct(protected SoundServiceV1 $soundServiceV1, protected FileUploadContract $fileUploadContract)
    {
    }

    public function index()
    {
        try {
            $response = $this->soundServiceV1->getAllSounds();
            if ($response) {
                return response(['success' => true, 'error' => null, 'data' => $response], Response::HTTP_OK);
            } else {
                return response(['success' => false, 'error' => $response], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function uploadSounds()
    {
        $files = File::files(public_path('assets/sounds/Meme'));

        $images = File::files(public_path('assets/sound_images/Meme'));

        $categoryId = 5;
        foreach ($files as $index => $file) {
            $this->fileUploadContract->setStorage(env('AZURE_STORAGE_CONTAINER'));
            $this->fileUploadContract->setPath(Config::get('path_constants.sounds').'/'.$categoryId.'/');
            $this->fileUploadContract->setContentType('audio/mpeg');
            $this->fileUploadContract->setExtension('.mp3');
            $asset = $this->fileUploadContract->uploadFile($file);

            $this->fileUploadContract->setPath(Config::get('path_constants.sounds').'/'.$categoryId.'/');
            $this->fileUploadContract->setContentType('image/png');
            $this->fileUploadContract->setExtension('.png');
            $image = $this->fileUploadContract->uploadFile($images[$index]);

            DB::table('sounds')->insert([
                'sound_category_id' => $categoryId,
                'file_name' => $asset['file_name'],
                'image' => $image['file_name'],
                'status' => Config::get('variable_constants.activation.active'),
                'created_at' => Carbon::now(),
            ]);
        }

        return true;

    }
}
