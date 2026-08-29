<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['cpc_question_option_id', 'language_id', 'text_value_translation', 'option_audio'])]
class CpcQuestionOptionTranslation extends Model
{
    protected $appends = ['option_audio_url'];

    public function option()
    {
        return $this->belongsTo(CpcQuestionOption::class, 'cpc_question_option_id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function getOptionAudioUrlAttribute(): ?string
    {
        return $this->option_audio ? asset("audios/cpc-questions/{$this->option_audio}") : null;
    }

    protected static function booted(): void
    {
        static::deleting(function (CpcQuestionOptionTranslation $translation) {
            if ($translation->option_audio) {
                $path = public_path("audios/cpc-questions/{$translation->option_audio}");

                if (file_exists($path)) {
                    @unlink($path);
                }
            }
        });
    }
}
