<?php

namespace App\Repositories;

use App\Core\Repositories\AbstractRepository;
use App\Models\QuestionTranslation;

class TranslationRepository extends AbstractRepository
{
    public function __construct(QuestionTranslation $model)
    {
        $this->model = $model;
    }

    public function languageCount()
    {
        return $this->model
            ->newQuery()
            ->selectRaw('language_id, status, count(*) as count')
            ->groupBy('language_id', 'status')
            ->get()
            ->groupBy('language_id');
    }

    public function needsAttention()
    {
        return $this->model
            ->newQuery()
            ->with('question:id', 'language:id,name')
            ->whereIn('status', ['partial', 'error'])
            ->orderByDesc('updated_at')
            ->paginate(50);
    }
}
