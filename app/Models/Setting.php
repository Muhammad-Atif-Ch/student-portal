<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_name',
        'logo',
        'favicon',
        'theme_layout',
        'sidebar_color',
        'color_theme',
        'mini_sidebar',
        'stiky_header',
        'image',
        'translation_api_key',
        'translation_api_region',
        'tts_api_key',
        'tts_api_region',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset("images/{$this->image}"); // or use Storage::url($this->image)
        }

        return null;
    }

    public static function cached(): ?self
    {
        return Cache::remember('app_settings', 3600, fn () => static::first());
    }

    public static function translationApiKey(): ?string
    {
        return static::cached()?->translation_api_key;
    }

    public static function ttsApiKey(): ?string
    {
        return static::cached()?->tts_api_key;
    }
}
