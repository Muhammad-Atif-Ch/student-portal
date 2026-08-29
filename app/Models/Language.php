<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    protected $table = "languages";

    protected $fillable = [
        'family',
        'name',
        'native_name',
        'code',
        'code_2',
        'country_code',
        'status',
        'app_1_show',
        'app_2_show',
        'app_3_show',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($language) {
            $language->questionTranslations()->delete();
        });
    }

    public function questionTranslations()
    {
        return $this->hasMany(QuestionTranslation::class);
    }

    public function voices()
    {
        return $this->hasMany(LanguageVoice::class);
    }
}
