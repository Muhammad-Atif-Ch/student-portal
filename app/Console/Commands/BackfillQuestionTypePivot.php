<?php

namespace App\Console\Commands;

use App\Models\Question;
use App\Models\QuestionType;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:backfill-question-type-pivot')]
#[Description('Command description')]
class BackfillQuestionTypePivot extends Command
{
    protected $signature = 'questions:backfill-type-pivot';
    protected $description = 'Explode questions.type JSON into question_type_pivot rows';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = 0;

        Question::whereNotNull('type')->chunkById(200, function ($questions) use (&$count) {
            foreach ($questions as $question) {
                $types = is_array($question->type) ? $question->type : [];

                foreach ($types as $type) {
                    QuestionType::firstOrCreate([
                        'question_id' => $question->id,
                        'type' => $type,
                    ]);
                    $count++;
                }
            }
        });

        $this->info("Backfilled {$count} type-pivot rows.");
    }
}
