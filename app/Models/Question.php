<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'question',
        'correct_answer',
        'a',
        'b',
        'c',
        'd',
        'image',
        'answer_explanation',
        'visual_explanation',
        'audio_file',
        'type',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function studentQuizHistory()
    {
        return $this->hasMany(StudentQuizHistory::class, 'question_id');
    }
}
