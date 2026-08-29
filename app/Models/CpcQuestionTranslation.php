<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['cpc_question_id', 'language_id', 'question_translation', 'answer_explanation_translation', 'question_audio', 'answer_explanation_audio', 'status'])]
class CpcQuestionTranslation extends Model
{
    protected $appends = ['question_audio_url', 'answer_explanation_audio_url'];

    public function question()
    {
        return $this->belongsTo(CpcQuestion::class, 'cpc_question_id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function getQuestionAudioUrlAttribute(): ?string
    {
        return $this->question_audio ? asset("audios/cpc-questions/{$this->question_audio}") : null;
    }

    public function getAnswerExplanationAudioUrlAttribute(): ?string
    {
        return $this->answer_explanation_audio ? asset("audios/cpc-questions/{$this->answer_explanation_audio}") : null;
    }

    protected static function booted(): void
    {
        static::deleting(function (CpcQuestionTranslation $translation) {
            if ($translation->question_audio) {
                $path = public_path("audios/cpc-questions/{$translation->question_audio}");

                if (file_exists($path)) {
                    @unlink($path);
                }
            }

            if ($translation->answer_explanation_audio) {
                $path = public_path("audios/cpc-questions/{$translation->answer_explanation_audio}");

                if (file_exists($path)) {
                    @unlink($path);
                }
            }
        });
    }
}
