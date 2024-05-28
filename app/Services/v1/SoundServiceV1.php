<?php

namespace App\Services\v1;

use App\Repositories\v1\SoundRepositoryV1;

class SoundServiceV1
{
    public function __construct(protected SoundRepositoryV1 $soundRepositoryV1)
    {

    }

    public function getAllSounds()
    {
        return $this->soundRepositoryV1->getAllSounds();
    }
}
