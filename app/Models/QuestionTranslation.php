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
        'language_id',
        'title_audio_file',
        'a_audio_file',
        'b_audio_file',
        'c_audio_file',
        'd_audio_file',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function Language()
    {
        return $this->belongsTo(Language::class);
    }
}
