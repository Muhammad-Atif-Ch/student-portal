<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['cpc_case_study_id', 'language_id', 'title_translation', 'status'])]
class CpcCaseStudyTranslation extends Model
{
    public function caseStudy()
    {
        return $this->belongsTo(CpcCaseStudy::class, 'cpc_case_study_id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}
