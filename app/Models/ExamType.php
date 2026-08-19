<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'total_questions', 'passing_marks', 'total_time_minutes'])]

class ExamType extends Model
{
    public function poolRules()
    {
        return $this->hasMany(ExamPoolRule::class);
    }

    public function targetTypes()
    {
        return $this->hasMany(ExamTypeTargetType::class);
    }
}
