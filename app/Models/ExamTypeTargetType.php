<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['exam_type_id', 'type'])]

class ExamTypeTargetType extends Model
{
    public $timestamps = false;
}
