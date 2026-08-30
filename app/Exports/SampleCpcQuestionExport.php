<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class SampleCpcQuestionExport implements FromArray
{
    public function array(): array
    {
        return [
            ['Question', 'Option A', 'Option B', 'Option C', 'Option D', 'Correct Option', 'Explanation', 'Case Study ID'],
            ['What is 2+2?', '1', '2', '3', '4', 'D', 'Simple addition', ''],
            ['Capital of Pakistan?', 'Lahore', 'Karachi', 'Islamabad', 'Peshawar', 'C', 'Islamabad is the capital', '1'],
        ];
    }
}
