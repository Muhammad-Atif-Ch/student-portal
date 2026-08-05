<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable(['source_term', 'language_id', 'target_term'])]
class TranslationGlossary extends Model
{
    protected static function booted(): void
    {
        static::saved(fn (self $glossary) => static::forgetCache($glossary));
        static::deleted(fn (self $glossary) => static::forgetCache($glossary));
    }

    private static function forgetCache(self $glossary): void
    {
        Cache::forget("glossary_lang_{$glossary->language_id}");

        // language_id changed on update — also clear the old key
        if ($glossary->wasChanged('language_id')) {
            Cache::forget("glossary_lang_{$glossary->getOriginal('language_id')}");
        }
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}
