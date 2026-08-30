<?php

namespace App\Services\CpcCaseStudy;

use App\Core\Contracts\Responses\AbstractResponseInterface;
use App\Core\Services\AbstractService;
use App\Helpers\ResponseCode;
use App\Helpers\UploadFile;
use App\Http\Requests\CpcCaseStudy\CreateCpcCaseStudyRequest;
use App\Http\Requests\CpcCaseStudy\UpdateCpcCaseStudyRequest;
use App\Models\CpcCaseStudy;
use App\Repositories\CpcCaseStudyRepository;
use App\Responses\CpcCaseStudyResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CpcCaseStudyService extends AbstractService
{
    public function __construct(
        CpcCaseStudyRepository $repository,
        CpcCaseStudyResponse $response,
        Request $request,
        private UploadFile $uploadFile,
    ) {
        $this->repository = $repository;
        $this->response = $response;
        $this->request = $request;
    }

    public function createCaseStudy(CreateCpcCaseStudyRequest $request): AbstractResponseInterface
    {
        $data = $request->validated();

        try {
            DB::transaction(function () use ($data, $request) {
                $caseStudy = $this->create(['title' => $data['title'], 'cpc_type_id' => $data['cpc_type_id']]);

                $this->storeBlocks($caseStudy, $request, $data['blocks']);
            });

            $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getCreateResponseMessage());
        } catch (\Exception $e) {
            $this->response->setResponse(ResponseCode::ERROR, $e->getCode(), $e->getMessage());
        }

        return $this->response;
    }

    public function listCaseStudies(): LengthAwarePaginator
    {
        $this->setLimit(50);

        return $this->repository->getList(['type', 'blocks', 'cpcQuestions']);
    }

    public function listCaseStudiesForTranslation(): LengthAwarePaginator
    {
        $this->setLimit(50);

        return $this->repository->getList(['blocks.translations', 'translations.language', 'cpcQuestions']);
    }

    public function showCaseStudy($id): Model
    {
        return $this->getById($id, ['type', 'blocks.translations.language', 'translations.language', 'cpcQuestions.options']);
    }

    public function updateCaseStudy(UpdateCpcCaseStudyRequest $request, $id): AbstractResponseInterface
    {
        $data = $request->validated();

        try {
            DB::transaction(function () use ($data, $request, $id) {
                $caseStudy = $this->repository->getById($id, ['blocks']);

                $this->update(['title' => $data['title'], 'cpc_type_id' => $data['cpc_type_id']], $id);

                $keepIds = collect($data['blocks'])->pluck('id')->filter()->all();
                $caseStudy->blocks->whereNotIn('id', $keepIds)->each->delete();

                $this->storeBlocks($caseStudy, $request, $data['blocks'], replace: true);
            });

            $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getUpdateResponseMessage());
        } catch (\Exception $e) {
            $this->response->setResponse(ResponseCode::ERROR, $e->getCode(), $e->getMessage());
        }

        return $this->response;
    }

    private function storeBlocks(CpcCaseStudy $caseStudy, Request $request, array $blocksData, bool $replace = false): void
    {
        $existingBlocks = $replace ? $caseStudy->blocks->keyBy('id') : collect();

        $sortOrder = 0;

        foreach ($blocksData as $key => $block) {
            $existing = ! empty($block['id']) ? $existingBlocks->get((int) $block['id']) : null;

            $payload = [
                'type' => $block['type'],
                'sort_order' => $sortOrder++,
                'content' => null,
                'items' => null,
                'list_style' => null,
            ];

            if ($block['type'] === 'text') {
                $payload['content'] = $block['content'] ?? null;
            } elseif ($block['type'] === 'list') {
                $lines = preg_split('/\r\n|\r|\n/', (string) ($block['items_text'] ?? ''));
                $items = array_values(array_filter(array_map('trim', $lines), fn ($item) => $item !== ''));
                $payload['items'] = $items;
                $payload['list_style'] = $block['list_style'] ?? 'bullet';
            }

            $file = $request->file("blocks.$key.image");

            if ($block['type'] === 'image') {
                if ($file) {
                    if ($existing?->file_path) {
                        $this->deleteFile($existing->file_path);
                    }
                    $payload['file_path'] = $this->uploadFile->upload('cpc-case-studies', $file);
                } else {
                    $payload['file_path'] = $existing?->file_path;
                }
            } else {
                if ($existing?->file_path) {
                    $this->deleteFile($existing->file_path);
                }
                $payload['file_path'] = null;
            }

            if ($existing) {
                $existing->update($payload);
            } else {
                $caseStudy->blocks()->create($payload);
            }
        }
    }

    private function deleteFile(?string $file): void
    {
        if (! $file) {
            return;
        }

        $path = public_path("cpc-case-studies/{$file}");

        if (file_exists($path)) {
            @unlink($path);
        }
    }
}
