<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'question_id',
        'lenguage_id',
        'question_translation',
        'a_translation',
        'b_translation',
        'c_translation',
        'd_translation',
        'answer_explanation_translation',
        'question_audio',
        'a_audio',
        'b_audio',
        'c_audio',
        'd_audio',
        'answer_explanation_audio'
    ];

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    public function lenguage()
    {
        return $this->belongsTo(Lenguage::class, 'lenguage_id');
    }
}
