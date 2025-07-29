<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LanguageVoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'language_id',
        'gender',
        'locale',
        'name',
    ];

    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}
