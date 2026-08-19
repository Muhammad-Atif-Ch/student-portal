<?php

namespace App\Imports;

use App\Models\Question;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class QuestionImport implements SkipsOnFailure, ToModel, WithStartRow, WithValidation
{
    use SkipsFailures;

    private const ANSWER_MAP = [
        'option1' => 'a',
        'option2' => 'b',
        'option3' => 'c',
        'option4' => 'd',
    ];

    private const ALLOWED_TYPES = ['car', 'bike', 'bus', 'truck'];

    private array $pendingTypes = [];

    public function __construct(private $quiz_id)
    {
        Question::created(function (Question $question) {
            $key = spl_object_id($question);

            if (! empty($this->pendingTypes[$key])) {
                $question->type()->createMany(array_map(fn ($type) => ['type' => $type], $this->pendingTypes[$key]));
                unset($this->pendingTypes[$key]);
            }
        });
    }

    public function model(array $row)
    {
        $correctAnswer = self::ANSWER_MAP[strtolower(preg_replace('/\s+/', '', (string) $row[2]))] ?? null;

        $types = collect(explode(',', (string) $row[8]))
            ->map(fn ($t) => strtolower(trim($t)))
            ->filter(fn ($t) => in_array($t, self::ALLOWED_TYPES, true))
            ->values()
            ->all();

        $question = new Question([
            'quiz_id' => $this->quiz_id,
            'question' => $row[1],
            'correct_answer' => $correctAnswer,
            'a' => $row[3],
            'b' => $row[4],
            'c' => $row[5],
            'd' => $row[6],
            'answer_explanation' => $row[7],
        ]);
        $this->pendingTypes[spl_object_id($question)] = $types;

        return $question;
    }

    public function rules(): array
    {
        return [
            '1' => ['required', 'string'],
            '2' => ['required', 'string', function ($attribute, $value, $fail) {
                if (! isset(self::ANSWER_MAP[strtolower(preg_replace('/\s+/', '', (string) $value))])) {
                    $fail('Correct answer must be Option1, Option2, Option3 or Option4.');
                }
            }],
            '3' => ['required', 'string'], // a
            '4' => ['required', 'string'], // b
            '8' => ['nullable', 'string'],
        ];
    }

    public function customValidationAttributes(): array
    {
        return [
            '1' => 'Question',
            '2' => 'Correct Answer',
            '3' => 'Option A',
            '4' => 'Option B',
        ];
    }

    public function startRow(): int
    {
        return 2;
    }
}
