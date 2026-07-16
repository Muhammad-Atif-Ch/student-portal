<?php

namespace App\Services\Translation;

use App\Models\Language;
use App\Models\Question;
use App\Repositories\TranslationRepository;

class TranslationReportService
{
    public function __construct(private TranslationRepository $repository) {}

    public function getReport(): array
    {
        $totalQuestions = Question::whereNotNull('question')->where('question', '!=', '')->count();
        $languages = Language::where('status', 1)->get();

        $counts = $this->repository->languageCount();

        $byLanguage = $languages->map(function ($language) use ($totalQuestions, $counts) {
            $byStatus = $counts->get($language->id, collect())->pluck('count', 'status');
            $completed = (int) $byStatus->get('completed', 0);
            $partial = (int) $byStatus->get('partial', 0);
            $errored = (int) $byStatus->get('error', 0);

            return [
                'language_id' => $language->id,
                'language' => $language->name,
                'total' => $totalQuestions,
                'completed' => $completed,
                'partial' => $partial,
                'errored' => $errored,
                'not_started' => max(0, $totalQuestions - $completed - $partial - $errored),
            ];
        });

        $needsAttention = $this->repository->needsAttention();

        return ['by_language' => $byLanguage, 'needs_attention' => $needsAttention];
    }
}
