<?php

namespace App\Imports;

use App\Models\Question;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class QuestionImport implements ToModel, WithStartRow
{
    public function __construct(private $quiz_id)
    {
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $cleanedString = strtolower(preg_replace('/\s+/', '', $row[2]));
        $correct_answer = null;
        if ($cleanedString == 'option1') {
            $correct_answer = 'a';
        } elseif ($cleanedString == 'option2') {
            $correct_answer = 'b';
        } elseif ($cleanedString == 'option3') {
            $correct_answer = 'c';
        } elseif ($cleanedString == 'option4') {
            $correct_answer = 'd';
        } else {
            $correct_answer = null;
        }
        //dd($row, $correct_answer, $cleanedString);
        if ($row[1] !== null) {
            return new Question([
                'quiz_id' => $this->quiz_id,
                'question' => $row[1],
                'correct_answer' => $correct_answer,
                'a' => $row[3],
                'b' => $row[4],
                'c' => $row[5],
                'd' => $row[6],
                'answer_explanation' => $row[7],
                'type' => strtolower($row[8]),
            ]);
        }
    }

    public function startRow(): int
    {
        return 3; // Start reading from the second row
    }
}
