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
        'e',
        'f',
        'answer_explanation',
        'audio_file',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
