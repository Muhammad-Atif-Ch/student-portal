<?php

namespace App\Services\Translation\Glossary;

use App\Core\Contracts\Responses\AbstractResponseInterface;
use App\Core\Services\AbstractService;
use App\Helpers\ResponseCode;
use App\Http\Requests\TranslationGlossary\CreateTranslationGlossaryRequest;
use App\Http\Requests\TranslationGlossary\ImportTranslationGlossaryRequest;
use App\Http\Requests\TranslationGlossary\UpdateTranslationGlossaryRequest;
use App\Imports\TranslationGlossaryImport;
use App\Repositories\TranslationGlossaryRepository;
use App\Responses\TranslationGlossaryResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class TranslationGlossaryService extends AbstractService
{
    public function __construct(
        TranslationGlossaryRepository $repository,
        TranslationGlossaryResponse $response,
        Request $request,
    ) {
        $this->repository = $repository;
        $this->response = $response;
        $this->request = $request;
    }

    public function createTranslationGlossary(CreateTranslationGlossaryRequest $request): AbstractResponseInterface
    {
        try {
            $data = $request->validated();
            $this->create($data);

            $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getCreateResponseMessage());
        } catch (\Exception $e) {
            $this->response->setResponse(ResponseCode::ERROR, $e->getCode(), $e->getMessage());
        }

        return $this->response;
    }

    public function listTranslationGlossary(): LengthAwarePaginator
    {
        $this->setLimit(50);

        return $this->repository->getByCondition([]);
    }

    public function showTranslationGlossary($id): Model
    {
        return $this->getWhere(['id' => $id]);
    }

    public function updateTranslationGlossary(UpdateTranslationGlossaryRequest $request, $id): AbstractResponseInterface
    {
        try {
            $data = $request->validated();

            $this->update($data, $id);

            $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getUpdateResponseMessage());
        } catch (\Exception $e) {
            $this->response->setResponse(ResponseCode::ERROR, $e->getCode(), $e->getMessage());
        }

        return $this->response;
    }

    public function importGlossary(ImportTranslationGlossaryRequest $request): AbstractResponseInterface
    {
        $data = $request->validated();
        try {
            DB::beginTransaction();

            $import = new TranslationGlossaryImport($data['language_id']);
            Excel::import($import, $request->file('file'));

            $failures = $import->failures();

            // foreach ($import->getTouchedLanguageIds() as $languageId) {
            //     Cache::forget("glossary_lang_{$languageId}");
            // }

            Log::info('Glossary import completed', [
                'imported_count' => $import->getImportedCount(),
                'failed_count' => $failures->count(),
                'languages_affected' => $import->getTouchedLanguageIds(),
            ]);

            if ($failures->isNotEmpty()) {
                DB::commit(); // keep the successfully imported rows, don't roll them back

                $errors = $failures->map(fn ($failure) => [
                    'row' => $failure->row(),
                    'attribute' => $failure->attribute(),
                    'errors' => $failure->errors(),
                    'values' => $failure->values(),
                ])->toArray();

                $this->response->setResponse(
                    ResponseCode::ERROR,
                    422,
                    sprintf('%d term(s) imported, %d row(s) failed validation.', $import->getImportedCount(), $failures->count()),
                    $errors
                );

                return $this->response;
            }

            DB::commit();
            $this->response->setResponse(
                ResponseCode::SUCCESS,
                ResponseCode::REGULAR,
                sprintf('%d glossary term(s) imported successfully.', $import->getImportedCount())
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Glossary import failed: '.$e->getMessage());
            $this->response->setResponse(ResponseCode::ERROR, 500, 'Import failed: '.$e->getMessage());
        }

        return $this->response;
    }

    public function destroyAll()
    {
        $glossary = $this->repository->getListWithoutPagination();
        foreach ($glossary as $g) {
            $this->destroy($g);
        }

        $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getDeleteResponseMessage());

        return $this->response;
    }
}
