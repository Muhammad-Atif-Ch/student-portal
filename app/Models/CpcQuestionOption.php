<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['cpc_question_id', 'option_key', 'type', 'text_value', 'file_path', 'is_correct'])]
class CpcQuestionOption extends Model
{
    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function cpcQuestion()
    {
        return $this->belongsTo(CpcQuestion::class);
    }

    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path ? asset("cpc-questions/{$this->file_path}") : null;
    }

    public function translations()
    {
        return $this->hasMany(CpcQuestionOptionTranslation::class);
    }

    protected static function booted(): void
    {
        static::deleting(function (CpcQuestionOption $option) {
            if ($option->file_path) {
                $path = public_path("cpc-questions/{$option->file_path}");

                if (file_exists($path)) {
                    @unlink($path);
                }
            }

            $option->translations->each->delete();
        });
    }
}
