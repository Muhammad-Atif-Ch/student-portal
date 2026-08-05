<?php

namespace App\Imports;

use App\Models\Question;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class QuestionImport implements ToModel, WithStartRow
{
    public function __construct(private $quiz_id) {}

    /**
     * @return Model|null
     */
    public function model(array $row)
    {
        $cleanedString = strtolower(preg_replace('/\s+/', '', $row[2]));
        $correct_answer = match ($cleanedString) {
            'option1' => 'a',
            'option2' => 'b',
            'option3' => 'c',
            'option4' => 'd',
            default => null,
        };

        if ($row[1] === null) {
            return null;
        }

        $allowedTypes = ['car', 'bike', 'bus', 'truck'];
        $types = collect(explode(',', (string) $row[8]))
            ->map(fn ($t) => strtolower(trim($t)))
            ->filter(fn ($t) => in_array($t, $allowedTypes, true))
            ->values()
            ->all();

        // dd($row, $correct_answer, $cleanedString);

        return new Question([
            'quiz_id' => $this->quiz_id,
            'question' => $row[1],
            'correct_answer' => $correct_answer,
            'a' => $row[3],
            'b' => $row[4],
            'c' => $row[5],
            'd' => $row[6],
            'answer_explanation' => $row[7],
            'type' => $types,
        ]);

    }

    public function startRow(): int
    {
        return 2; // Start reading from the second row
    }
}
