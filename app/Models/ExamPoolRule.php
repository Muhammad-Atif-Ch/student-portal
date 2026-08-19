<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['exam_type_id', 'quiz_id', 'pool_type', 'specific_type', 'required_count'])]
class ExamPoolRule extends Model
{
    public function examType()
    {
        return $this->belongsTo(ExamType::class);
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

}
