<?php

namespace App\Imports;

use App\Models\TranslationGlossary;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;

class TranslationGlossaryImport implements SkipsOnFailure, ToModel, WithHeadingRow, WithValidation
{
    use SkipsFailures;

    private int $importedCount = 0;

    private array $touchedLanguageIds = [];

    public function __construct(private int $languageId) {}

    public function model(array $row)
    {
        // $languageId = (int) trim($row['language_id']);
        $sourceTerm = trim($row['source_term']);
        $targetTerm = trim($row['target_term']);

        $glossary = TranslationGlossary::updateOrCreate(
            ['source_term' => $sourceTerm, 'language_id' => $this->languageId],
            ['target_term' => $targetTerm]
        );

        $this->importedCount++;
        // $this->touchedLanguageIds[$languageId] = true;

        Log::info('Glossary term imported', [
            'id' => $glossary->id,
            // 'language_id' => $languageId,
            'source_term' => $sourceTerm,
            'target_term' => $targetTerm,
            'was_recently_created' => $glossary->wasRecentlyCreated,
        ]);

        return $glossary;
    }

    public function rules(): array
    {
        return [
            'source_term' => 'required|string|max:255',
            // 'language_id' => 'required|integer|exists:languages,id',
            'target_term' => 'required|string|max:255',
        ];
    }

    // public function customValidationMessages(): array
    // {
    //     return [
    //         'language_id.exists' => 'Language ID :input does not exist.',
    //     ];
    // }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            Log::warning('Glossary import row failed validation', [
                'row' => $failure->row(),
                'attribute' => $failure->attribute(),
                'errors' => $failure->errors(),
                'values' => $failure->values(),
            ]);
        }

        $this->failures = array_merge($this->failures ?? [], $failures);
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getTouchedLanguageIds(): array
    {
        return array_keys($this->touchedLanguageIds);
    }
}
