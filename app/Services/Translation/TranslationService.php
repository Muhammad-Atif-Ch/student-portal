<?php

namespace App\Services\Translation;

use App\Models\Language;
use App\Models\Question;
use App\Responses\TranslationResponse;
use Illuminate\Support\Facades\Request;
use App\Core\Services\AbstractService;
use App\Repositories\TranslationRepository;

class TranslationService extends AbstractService
{
    public function __construct(TranslationRepository $repository, TranslationResponse $response, Request $request)
    {
        $this->repository = $repository;
        $this->response = $response;
        $this->request = $request;
    }

    public function index(array $filters)
    {
        $where = collect($filters)->filter(fn ($v) => ! is_null($v))->toArray();

        return $this->getByCondition($where, ['quiz:id', 'question:id', 'language:id,name']);
    }
}