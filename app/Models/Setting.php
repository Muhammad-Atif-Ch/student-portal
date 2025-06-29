<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset("images/{$this->image}"); // or use Storage::url($this->image)
        }

        return null;
    }
}
