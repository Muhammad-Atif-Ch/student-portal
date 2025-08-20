<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreviousTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'test_datetime',
        'type',
        'question_type',
        'question_ids',
        'correct_answers',
        'incorrect_answers',
        'total_attempts',
    ];

    public function previousTestQuizes()
    {
        return $this->hasMany(PreviousTestQuiz::class, 'previous_test_id');
    }
}
