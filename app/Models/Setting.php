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
    ];
}
