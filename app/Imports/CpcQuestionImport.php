<?php

namespace App\Imports;

use App\Models\CpcCaseStudy;
use App\Models\CpcQuestion;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class CpcQuestionImport implements SkipsOnFailure, ToModel, WithStartRow, WithValidation
{
    use SkipsFailures;

    private const OPTION_KEYS = ['a', 'b', 'c', 'd'];

    private array $pendingOptions = [];

    public function __construct()
    {
        CpcQuestion::created(function (CpcQuestion $question) {
            $key = spl_object_id($question);

            if (! empty($this->pendingOptions[$key])) {
                $question->options()->createMany($this->pendingOptions[$key]);
                unset($this->pendingOptions[$key]);
            }
        });
    }

    public function model(array $row)
    {
        $correctOption = strtolower(trim((string) $row[5]));

        $question = new CpcQuestion([
            'question' => $row[0],
            'answer_explanation' => $row[6] ?? null,
            'cpc_case_study_id' => $this->resolveCaseStudyId($row[7] ?? null),
        ]);

        $options = [];
        foreach (self::OPTION_KEYS as $index => $key) {
            $options[] = [
                'option_key' => $key,
                'type' => 'text',
                'text_value' => $row[1 + $index],
                'is_correct' => $key === $correctOption,
            ];
        }

        $this->pendingOptions[spl_object_id($question)] = $options;

        return $question;
    }

    private function resolveCaseStudyId($id): ?int
    {
        $id = trim((string) $id);

        if ($id === '' || ! CpcCaseStudy::whereKey($id)->exists()) {
            return null;
        }

        return (int) $id;
    }

    public function rules(): array
    {
        return [
            '0' => ['required', 'string'], // question
            '1' => ['required', 'string'], // option a
            '2' => ['required', 'string'], // option b
            '5' => ['required', 'string', function ($attribute, $value, $fail) {
                if (! in_array(strtolower(trim((string) $value)), self::OPTION_KEYS, true)) {
                    $fail('Correct option must be A, B, C or D.');
                }
            }],
        ];
    }

    public function customValidationAttributes(): array
    {
        return [
            '0' => 'Question',
            '1' => 'Option A',
            '2' => 'Option B',
            '5' => 'Correct Option',
        ];
    }

    public function startRow(): int
    {
        return 2;
    }
}
