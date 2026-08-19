<?php

namespace App\Models;

use App\Models\Question;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['question_id', 'type'])]
class QuestionType extends Model
{
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
