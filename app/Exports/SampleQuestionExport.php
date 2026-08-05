<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class SampleQuestionExport implements FromArray
{
    public function array(): array
    {
        return [
            ['Question', 'Answer', 'Option 1', 'Option 2', 'Option 3', 'Option 4', 'Explanation', 'Type'],
            ['What is 2+2?', 'Option 4', '1', '2', '3', '4', 'plus', 'truck'],
            ['Capital of Pakistan?', 'Option 3', 'Lahore', 'Karachi', 'Islamabad', 'Peshawar', 'country and city name', 'truck, bus'],
        ];
    }
}
