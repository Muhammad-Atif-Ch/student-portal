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

    public function studentQuizHistory()
    {
        return $this->hasMany(StudentQuizHistory::class, 'question_id');
    }

    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('images/' . $this->image); // or use Storage::url($this->image)
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
