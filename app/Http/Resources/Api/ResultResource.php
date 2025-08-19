<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use App\Http\Resources\PreviousTestQuizResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ResultResource extends JsonResource
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
            'user_id' => $this->user_id,
            'test_datetime' => $this->test_datetime,
            'type' => $this->type,
            'question_type' => $this->question_type,
            'correct_answers' => $this->correct_answers,
            'incorrect_answers' => $this->incorrect_answers,
            'total_attempts' => $this->total_attempts,
            'quizzes' => PreviousTestQuizResource::collection($this->whenLoaded('PreviousTestQuizes')),
        ];
    }
}
