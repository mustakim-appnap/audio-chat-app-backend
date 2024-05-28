<?php

namespace App\Contracts;

interface FileUploadContract
{
    public function setStorage($storage_name);

    public function setPath($path);

    public function getStorage();

    public function uploadFile($file);

    public function fileExists($url);

    public function delete();

    public function upload_base64_file($base64_string);

    public function setContentType($content_type);

    public function setExtension($extension);
}
