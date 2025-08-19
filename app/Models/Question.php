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
        'question_translation',
        'a_translation',
        'b_translation',
        'c_translation',
        'd_translation',
        'answer_explanation_translation',
        'visual_explanation',
        'audio_file',
        'type',
    ];

    protected $appends = ['image_url', 'visual_explanation_url'];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function translations()
    {
        return $this->hasMany(QuestionTranslation::class, 'question_id')->forLanguage();
    }
    public function studentQuizHistories()
    {
        return $this->hasOne(StudentQuizHistory::class, 'question_id');
    }

    public function getPreviousQuestionsAttribute()
    {
        if ($this->previousTestQuiz && $this->previousTestQuiz->question_ids) {
            $questionIds = json_decode($this->previousTestQuiz->question_ids, true);
            return Question::whereIn('id', $questionIds)->get();
        }

        return collect();
    }

    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset("images/{$this->image}"); // or use Storage::url($this->image)
        }

        return null;
    }

    public function getVisualExplanationUrlAttribute()
    {
        if ($this->visual_explanation) {
            return asset('images/' . $this->visual_explanation); // or use Storage::url($this->image)
        }

        return null;
    }
}
