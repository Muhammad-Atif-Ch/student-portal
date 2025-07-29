<?php

namespace App\Repositories;

use App\Models\Language;
use App\Core\Repositories\AbstractRepository;
use App\Models\LanguageVoice;

class LanguageVoiceRepository extends AbstractRepository
{
    public function __construct(LanguageVoice $model)
    {
        $this->model = $model;
    }
}
