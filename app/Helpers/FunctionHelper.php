<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;

class FunctionHelper
{
    public static function asset_base_url($filePath)
    {
        if (env('APP_ENV') != 'local') {
            $blobClient = BlobRestProxy::createBlobService(env('AZURE_STORAGE_CONNECTION_STRING'));

            return $blobClient->getBlobUrl(env('AZURE_STORAGE_CONTAINER'), $filePath);
        }

        return Storage::disk('public')->url($filePath);

    }
}
