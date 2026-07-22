<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['source_term', 'language_id', 'target_term'])]
class TranslationGlossary extends Model
{
    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}
