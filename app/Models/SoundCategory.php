<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SoundCategory extends Model
{
    use HasFactory;

    protected $table = 'sound_categories';

    protected $guarded = [];

    public function sounds(): HasMany
    {
        return $this->hasMany(Sound::class, 'sound_category_id');
    }
}
