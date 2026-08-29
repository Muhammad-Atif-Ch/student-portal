<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['technical_dictionary_id', 'language_id', 'explanation_translation', 'explanation_audio', 'status'])]
class TechnicalDictionaryTranslation extends Model
{
    protected $appends = ['explanation_audio_url'];

    public function dictionary()
    {
        return $this->belongsTo(TechnicalDictionary::class, 'technical_dictionary_id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function getExplanationAudioUrlAttribute(): ?string
    {
        return $this->explanation_audio ? asset("audios/technical-dictionary/{$this->explanation_audio}") : null;
    }

    protected static function booted(): void
    {
        static::deleting(function (TechnicalDictionaryTranslation $translation) {
            if ($translation->explanation_audio) {
                $path = public_path("audios/technical-dictionary/{$translation->explanation_audio}");

                if (file_exists($path)) {
                    @unlink($path);
                }
            }
        });
    }
}
