<?php

namespace App\Services\v1;

use App\Contracts\FileUploadContract;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Blob\Models\CreateBlockBlobOptions;

class FileUploadServiceV1 implements FileUploadContract
{
    private $storage;

    private $path;

    private $content_type;

    private $extension;

    public function __construct()
    {
        $this->storage = Config::get('variable_constants.app_env') != 'local' ? Config::get('variable_constants.azure.container') : 'public';
    }

    public function setStorage($storage_name)
    {
        $this->storage = $storage_name;
    }

    public function getStorage()
    {
        return $this->storage;
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function setContentType($content_type)
    {
        $this->content_type = $content_type;
    }

    public function setExtension($extension)
    {
        $this->extension = $extension;
    }

    public function uploadFile($file)
    {
        $file_name = md5(strval(time() * 1000)).'.'.$file->getClientOriginalExtension();
        // @phpstan-ignore-next-line
        // $extension = $this->extension ? $this->extension : '.'. $file->getClientOriginalExtension();
        // $file_name = md5(strval(time() * 1000)) . $extension;
        $file_path = $this->path.$file_name;
        try {
            if (Config::get('variable_constants.app_env') != 'local') {
                /** Upload in Azure Container */
                $blobClient = BlobRestProxy::createBlobService(Config::get('variable_constants.azure.connection_string'));
                $options = new CreateBlockBlobOptions();
                $options->setContentType($this->content_type ?: $file->getClientMimeType());
                $blobClient->createBlockBlob($this->storage, $file_path, file_get_contents($file), $options);
            } else {
                Storage::disk($this->storage)->put($file_path, file_get_contents($file), 'public');
            }

            return [
                'file_name' => $file_name,
                'file_path' => $file_path,
            ];
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public function delete()
    {
        return Storage::disk($this->storage)->delete($this->path);
    }

    public function fileExists($filePath)
    {
        return Storage::disk($this->storage)->exists($filePath);
    }

    public function upload_base64_file($base64_string)
    {
        $explode_string = explode(',', $base64_string);
        $file_name = md5(strval(time() * 1000)).'.png';
        $file_path = $this->path.$file_name;
        try {
            // $base64_string = str_replace('data:image/png;base64,', '', $base64_string);
            //   $base64_string = str_replace(' ', '+', $base64_string);
            if (env('APP_ENV') != 'local') {
                /** Upload in Azure Container */
                $blobClient = BlobRestProxy::createBlobService(env('AZURE_STORAGE_CONNECTION_STRING'));
                $options = new CreateBlockBlobOptions();
                $options->setContentType('image/png');
                $blobClient->createBlockBlob($this->storage, $file_path, (base64_decode(end($explode_string))), $options);
            } else {
                Storage::disk($this->storage)->put($file_path, (base64_decode($base64_string)), 'public');
            }

            return [
                'file_name' => $file_name,
                'file_path' => $file_path,
            ];
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }
}
