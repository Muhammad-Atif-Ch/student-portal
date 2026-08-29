<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['cpc_case_study_id', 'type', 'content', 'items', 'list_style', 'file_path', 'sort_order'])]
class CpcCaseStudyBlock extends Model
{
    protected $casts = [
        'items' => 'array',
    ];

    protected $appends = ['file_url'];

    public function caseStudy()
    {
        return $this->belongsTo(CpcCaseStudy::class, 'cpc_case_study_id');
    }

    public function translations()
    {
        return $this->hasMany(CpcCaseStudyBlockTranslation::class);
    }

    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path ? asset("cpc-case-studies/{$this->file_path}") : null;
    }

    protected static function booted(): void
    {
        static::deleting(function (CpcCaseStudyBlock $block) {
            if ($block->file_path) {
                $path = public_path("cpc-case-studies/{$block->file_path}");

                if (file_exists($path)) {
                    @unlink($path);
                }
            }

            $block->translations->each->delete();
        });
    }
}
