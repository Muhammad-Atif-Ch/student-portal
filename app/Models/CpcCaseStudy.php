<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['title', 'cpc_type_id'])]
class CpcCaseStudy extends Model
{
    public function type(): BelongsTo
    {
        return $this->belongsTo(CpcType::class, 'cpc_type_id');
    }

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
