<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'official_test_question',
    ];

    public function questions()
    {
        return $this->hasMany(Question::class, 'quiz_id');
    }

    public function previousTestQuiz()
    {
        return $this->hasMany(PreviousTestQuiz::class);
    }
}
