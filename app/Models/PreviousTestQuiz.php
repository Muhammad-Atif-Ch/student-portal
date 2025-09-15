<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreviousTestQuiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'previous_test_id',
        'quiz_id',
        'question_id',
        'type',
        'answer',
        'correct',
    ];

    public function previousTest()
    {
        return $this->belongsTo(PreviousTest::class, 'previous_test_id');
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }
}
