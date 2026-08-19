<?php

namespace Database\Seeders;

use App\Models\ExamPoolRule;
use App\Models\ExamType;
use App\Models\ExamTypeTargetType;
use Illuminate\Database\Seeder;

class ExamFormulaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedExamType(
            name: 'Car',
            total: 40,
            pass: 32,
            time: 45,
            targets: ['car'],
            rows: [
                1 => [5, ['car' => 3]],
                2 => [4, ['car' => 3]],
                3 => [14, ['car' => 8]],
                4 => [1, ['car' => 1]],
                // 5 => [null, ['car' => 1]],
                5 => [1, []],
            ],
        );

        $this->seedExamType(
            name: 'Bike',
            total: 40,
            pass: 32,
            time: 45, // adjust pass/time to actual values
            targets: ['bike'],
            rows: [
                // quiz_id => [common, ['bike' => n]]
                1 => [5, ['bike' => 3]],
                2 => [4, ['bike' => 3]],
                3 => [14, ['bike' => 8]],
                4 => [1, ['bike' => 1]],
                5 => [1, []],
            ],
        );

        $this->seedExamType(
            name: 'Bus',
            total: 100,
            pass: 74,
            time: 120,
            targets: ['bus'],
            rows: [
                1 => [13, ['bus' => 9]],
                2 => [11, ['bus' => 7]],
                3 => [26, ['bus' => 17]],
                4 => [4, ['bus' => 3]],
                5 => [6, ['bus' => 4]],
            ],
        );

        $this->seedExamType(
            name: 'Truck',
            total: 100,
            pass: 74,
            time: 120,
            targets: ['truck'],
            rows: [
                1 => [13, ['truck' => 9]],
                2 => [11, ['truck' => 7]],
                3 => [26, ['truck' => 17]],
                4 => [4, ['truck' => 3]],
                5 => [6, ['truck' => 4]],
            ],
        );

        $this->seedExamType(
            name: 'Combined Bus & Truck',
            total: 140,
            pass: 104,
            time: 180,
            targets: ['truck', 'bus'],
            rows: [
                1 => [19, ['truck' => 6, 'bus' => 6]],
                2 => [15, ['truck' => 5, 'bus' => 5]],
                3 => [36, ['truck' => 12, 'bus' => 12]],
                4 => [6, ['truck' => 2, 'bus' => 2]],
                5 => [8, ['truck' => 3, 'bus' => 3]],
            ],
        );

    }

    private function seedExamType(string $name, int $total, int $pass, int $time, array $targets, array $rows): void
    {
        $examType = ExamType::updateOrCreate(
            ['name' => $name],
            ['total_questions' => $total, 'passing_marks' => $pass, 'total_time_minutes' => $time]
        );

        foreach ($targets as $type) {
            ExamTypeTargetType::firstOrCreate(['exam_type_id' => $examType->id, 'type' => $type]);
        }

        foreach ($rows as $quizId => [$commonCount, $specifics]) {
            // if ($commonCount == null) {
            //     continue;
            // }

            ExamPoolRule::updateOrCreate(
                ['exam_type_id' => $examType->id, 'quiz_id' => $quizId, 'pool_type' => 'common', 'specific_type' => null],
                ['required_count' => $commonCount]
            );

            foreach ($specifics as $type => $count) {
                if ($count <= 0) {
                    continue;
                }

                ExamPoolRule::updateOrCreate(
                    ['exam_type_id' => $examType->id, 'quiz_id' => $quizId, 'pool_type' => 'specific', 'specific_type' => $type],
                    ['required_count' => $count]
                );
            }
        }
    }
}
