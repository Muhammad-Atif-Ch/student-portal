<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentQuizHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'quiz_id',
        'question_id',
        'answer',
        'correct',
        'type',
    ];

    public function student()
    {
        return $this->belongsTo(User::class);
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
