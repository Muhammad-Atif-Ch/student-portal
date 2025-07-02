<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    protected $fillable = [
        'family',
        'name',
        'native_name',
        'code',
        'code_2',
        'country_code',
        'status'
    ];
}
