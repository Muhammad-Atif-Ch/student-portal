<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'quiz_id',
        'question_id',
        'type',
    ];
}
