<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuestionTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'question_id',
        'language_id',
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

    protected $appends = [
        'question_audio_url',
        'a_audio_url',
        'b_audio_url',
        'c_audio_url',
        'd_audio_url',
        'answer_explanation_audio_url'
    ];

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id');
    }

    /**
     * Scope a query to only include translations for a specific language.
     */
    public function scopeForLanguage(Builder $query)
    {
        $deviceId = request()->header('Device-Id');
        $user = User::where('device_id', $deviceId)->first();
        $languageId = $user->language_id ?? 39;
        return $query->where('language_id', $languageId);
    }

    /**
     * Generate audio URL for a given field
     */
    protected function getAudioUrl($audioField)
    {
        return $this->{$audioField} ? asset('audios/' . $this->{$audioField}) : null;
    }

    /**
     * Get question audio URL
     */
    public function getQuestionAudioUrlAttribute()
    {
        return $this->getAudioUrl('question_audio');
    }

    /**
     * Get option A audio URL
     */
    public function getAAudioUrlAttribute()
    {
        return $this->getAudioUrl('a_audio');
    }

    /**
     * Get option B audio URL
     */
    public function getBAudioUrlAttribute()
    {
        return $this->getAudioUrl('b_audio');
    }

    /**
     * Get option C audio URL
     */
    public function getCAudioUrlAttribute()
    {
        return $this->getAudioUrl('c_audio');
    }

    /**
     * Get option D audio URL
     */
    public function getDAudioUrlAttribute()
    {
        return $this->getAudioUrl('d_audio');
    }

    /**
     * Get answer explanation audio URL
     */
    public function getAnswerExplanationAudioUrlAttribute()
    {
        return $this->getAudioUrl('answer_explanation_audio');
    }
}
