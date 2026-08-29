<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title'])]
class CpcCaseStudy extends Model
{
    public function blocks()
    {
        return $this->hasMany(CpcCaseStudyBlock::class)->orderBy('sort_order');
    }

    public function cpcQuestions()
    {
        return $this->hasMany(CpcQuestion::class)->orderBy('sort_order');
    }

    public function translations()
    {
        return $this->hasMany(CpcCaseStudyTranslation::class);
    }

    protected static function booted(): void
    {
        static::deleting(function (CpcCaseStudy $caseStudy) {
            $caseStudy->cpcQuestions()->update(['cpc_case_study_id' => null, 'sort_order' => 0]);
            $caseStudy->blocks->each->delete();
        });
    }
}
