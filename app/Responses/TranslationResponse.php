<?php

namespace App\Responses;

use App\Core\Responses\AbstractResponse;

class TranslationResponse extends AbstractResponse
{
    public function getCreateResponseMessage(): string
    {
        return 'Translation process started successfully';
    }

    public function getListResponseMessage(): string
    {
        return 'List of Translations get successfully';
    }

    public function getUpdateResponseMessage(): string
    {
        return 'Translation updated successfully';
    }

    public function getDeleteResponseMessage(): string
    {
        return 'Translation deleted successfully';
    }

    public function getStopResponseMessage(): string
    {
        return 'Translation process stopped successfully';
    }

    public function getRetranslateResponseMessage(): string
    {
        return 'Field translated successfully';
    }
}
