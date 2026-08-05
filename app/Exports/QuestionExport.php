<?php

namespace App\Exports;

use App\Models\Question;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class QuestionExport implements FromCollection, WithHeadings
{
    public function __construct(private $quiz_id) {}

    /**
     * @return Collection
     */
    public function collection()
    {
        return Question::where('quiz_id', $this->quiz_id)->get();
    }

    public function headings(): array
    {
        return [
            'Id',
            'Question',
            'Correct Answer',
            'Option A',
            'Option B',
            'Option C',
            'Option D',
            'Type',
            ''

        ];
    }
}
