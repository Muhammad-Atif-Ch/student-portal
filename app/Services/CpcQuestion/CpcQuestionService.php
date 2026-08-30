<?php

namespace App\Services\CpcQuestion;

use App\Core\Contracts\Responses\AbstractResponseInterface;
use App\Core\Services\AbstractService;
use App\Helpers\ResponseCode;
use App\Helpers\UploadFile;
use App\Http\Requests\CpcQuestion\CreateCpcQuestionRequest;
use App\Http\Requests\CpcQuestion\UpdateCpcQuestionRequest;
use App\Models\CpcQuestion;
use App\Repositories\CpcQuestionRepository;
use App\Responses\CpcQuestionResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CpcQuestionService extends AbstractService
{
    private const OPTION_KEYS = ['a', 'b', 'c', 'd'];

    public function __construct(
        CpcQuestionRepository $repository,
        CpcQuestionResponse $response,
        Request $request,
        private UploadFile $uploadFile,
    ) {
        $this->repository = $repository;
        $this->response = $response;
        $this->request = $request;
    }

    public function createCpcQuestion(CreateCpcQuestionRequest $request): AbstractResponseInterface
    {
        $data = $request->validated();

        try {
            DB::transaction(function () use ($data, $request) {
                $question = $this->create([
                    'question' => $data['question'],
                    'answer_explanation' => $data['answer_explanation'] ?? null,
                    'cpc_case_study_id' => $data['cpc_case_study_id'] ?? null,
                ]);

                $this->storeOptions($question, $request, $data['options'], $data['correct_option']);
            });

            $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getCreateResponseMessage());
        } catch (\Exception $e) {
            $this->response->setResponse(ResponseCode::ERROR, $e->getCode(), $e->getMessage());
        }

        return $this->response;
    }

    public function listCpcQuestions(): LengthAwarePaginator
    {
        $this->setLimit(50);

        return $this->repository->getList(['options', 'caseStudy']);
    }

    public function showCpcQuestion($id): Model
    {
        return $this->getById($id, ['options']);
    }

    public function updateCpcQuestion(UpdateCpcQuestionRequest $request, $id): AbstractResponseInterface
    {
        $data = $request->validated();

        try {
            DB::transaction(function () use ($data, $request, $id) {
                $question = $this->repository->getById($id, ['options']);

                $this->update([
                    'question' => $data['question'],
                    'answer_explanation' => $data['answer_explanation'] ?? null,
                    'cpc_case_study_id' => $data['cpc_case_study_id'] ?? null,
                ], $id);

                $this->storeOptions($question, $request, $data['options'], $data['correct_option'], replace: true);
            });

            $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getUpdateResponseMessage());
        } catch (\Exception $e) {
            $this->response->setResponse(ResponseCode::ERROR, $e->getCode(), $e->getMessage());
        }

        return $this->response;
    }

    private function storeOptions(CpcQuestion $question, Request $request, array $optionsData, string $correctOption, bool $replace = false): void
    {
        $existing = $replace ? $question->options->keyBy('option_key') : collect();

        foreach (self::OPTION_KEYS as $key) {
            $option = $optionsData[$key] ?? [];
            $current = $existing->get($key);

            $payload = [
                'option_key' => $key,
                'type' => $option['type'],
                'is_correct' => $key === $correctOption,
            ];

            $file = $request->file("options.$key.file");

            if ($option['type'] === 'file') {
                if ($file) {
                    if ($current?->file_path) {
                        $this->deleteFile($current->file_path);
                    }
                    $payload['file_path'] = $this->uploadFile->upload('cpc-questions', $file);
                } else {
                    $payload['file_path'] = $current?->file_path;
                }
                $payload['text_value'] = null;
            } else {
                if ($current?->file_path) {
                    $this->deleteFile($current->file_path);
                }
                $payload['text_value'] = $option['text_value'] ?? null;
                $payload['file_path'] = null;
            }

            if ($current) {
                $current->update($payload);
            } else {
                $question->options()->create($payload);
            }
        }
    }

    private function deleteFile(?string $file): void
    {
        if (! $file) {
            return;
        }

        $path = public_path("cpc-questions/{$file}");

        if (file_exists($path)) {
            @unlink($path);
        }
    }
}
