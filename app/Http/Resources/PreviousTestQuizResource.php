<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PreviousTestQuizResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'previous_test_id' => $this->previous_test_id,
            'quiz_name' => $this->whenLoaded('quiz', fn() => $this->quiz->title),
            'question_id' => $this->question_id,
            'type' => $this->type,
            'answer' => $this->answer,
            'correct' => $this->correct,
        ];
    }
}
