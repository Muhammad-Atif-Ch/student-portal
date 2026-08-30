<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['question', 'answer_explanation', 'cpc_case_study_id', 'sort_order'])]
class CpcQuestion extends Model
{
    public function options()
    {
        return $this->hasMany(CpcQuestionOption::class)->orderBy('option_key');
    }

    public function caseStudy()
    {
        return $this->belongsTo(CpcCaseStudy::class, 'cpc_case_study_id');
    }

    public function translations()
    {
        return $this->hasMany(CpcQuestionTranslation::class);
    }

    protected static function booted(): void
    {
        static::deleting(function (CpcQuestion $question) {
            $question->options->each->delete();
            $question->translations->each->delete();
        });
    }
}
