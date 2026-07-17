<?php

namespace App\Services\Tts;

use App\Models\Language;
use App\Models\QuestionTranslation;

class TtsReportService
{
    public function getReport(): array
    {
        $languages = Language::where('status', 1)->get();

        $rows = QuestionTranslation::whereIn('language_id', $languages->pluck('id'))->get();

        $byLanguage = $languages->map(function ($language) use ($rows) {
            $langRows = $rows->where('language_id', $language->id);
            $stats = ['completed' => 0, 'partial' => 0, 'errored' => 0, 'skipped' => 0];

            foreach ($langRows as $row) {
                $stats[$this->classifyStatus($row)]++;
            }

            return [
                'language_id' => $language->id,
                'name' => $language->name,
                'total' => $langRows->count(),
                'completed' => $stats['completed'],
                'partial' => $stats['partial'],
                'errored' => $stats['errored'],
                'skipped' => $stats['skipped'],
            ];
        });

        $needsAttention = QuestionTranslation::with('question:id', 'language:id,name')
            ->whereIn('language_id', $languages->pluck('id'))
            ->get()
            ->filter(fn ($row) => in_array($this->classifyStatus($row), ['partial', 'errored']))
            ->sortByDesc('updated_at')
            ->take(100)
            ->map(fn ($row) => [
                'question_id' => $row->question_id,
                'language' => $row->language,
                'language_id' => $row->language_id,
                'status' => $this->classifyStatus($row),
                'error' => $row->error,
            ])
            ->values();

        return [
            'by_language' => $byLanguage,
            'needs_attention' => ['data' => $needsAttention],
        ];
    }

    private function classifyStatus(QuestionTranslation $row): string
    {
        $hasText = false;
        $missingAudio = false;

        foreach (TtsActionService::FIELDS as $field) {
            $text = $row->{"{$field}_translation"};
            if (empty($text)) {
                continue;
            }

            $hasText = true;

            if (empty($row->{"{$field}_audio"})) {
                $missingAudio = true;
            }
        }

        if (! $hasText) {
            return 'skipped';
        }

        if ($row->status === 'error') {
            return 'errored';
        }

        return $missingAudio ? 'partial' : 'completed';
    }
}
