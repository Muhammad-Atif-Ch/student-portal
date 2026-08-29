<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'cpc_type_id',
    'title',
    'mode',
    'total_time_minutes',
    'total_questions',
    'passing_score',
    'min_marks_per_scenario',
    'is_active',
])]
class CpcExam extends Model
{
    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(CpcType::class, 'cpc_type_id');
    }
}
