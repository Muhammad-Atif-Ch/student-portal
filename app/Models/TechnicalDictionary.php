<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['term', 'explanation', 'image'])]
class TechnicalDictionary extends Model
{
    protected $appends = ['image_url'];

    public function translations()
    {
        return $this->hasMany(TechnicalDictionaryTranslation::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset("technical-dictionary/{$this->image}") : null;
    }

    protected static function booted(): void
    {
        static::deleting(function (TechnicalDictionary $dictionary) {
            if ($dictionary->image) {
                $path = public_path("technical-dictionary/{$dictionary->image}");

                if (file_exists($path)) {
                    @unlink($path);
                }
            }

            $dictionary->translations->each->delete();
        });
    }
}
