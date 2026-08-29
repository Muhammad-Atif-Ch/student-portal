<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['cpc_case_study_block_id', 'language_id', 'content_translation', 'items_translation', 'content_audio'])]
class CpcCaseStudyBlockTranslation extends Model
{
    protected $casts = [
        'items_translation' => 'array',
    ];

    protected $appends = ['content_audio_url'];

    public function block()
    {
        return $this->belongsTo(CpcCaseStudyBlock::class, 'cpc_case_study_block_id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function getContentAudioUrlAttribute(): ?string
    {
        return $this->content_audio ? asset("audios/cpc-case-studies/{$this->content_audio}") : null;
    }

    protected static function booted(): void
    {
        static::deleting(function (CpcCaseStudyBlockTranslation $translation) {
            if ($translation->content_audio) {
                $path = public_path("audios/cpc-case-studies/{$translation->content_audio}");

                if (file_exists($path)) {
                    @unlink($path);
                }
            }
        });
    }
}
