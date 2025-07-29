<?php

namespace App\Responses;

use App\Core\Responses\AbstractResponse;

class LanguageVoiceResponse extends AbstractResponse
{
    public function getCreateResponseMessage(): string
    {
        return "Language Vocie created successfully";
    }

    public function getListResponseMessage(): string
    {
        return "List of Language voices get successfully";
    }

    public function getUpdateResponseMessage(): string
    {
        return "Language voice updated successfully";
    }

    public function getDeleteResponseMessage(): string
    {
        return "Language voice deleted successfully";
    }
    public function getRecordResponseMessage(): string
    {
        return "Language voice get successfully";
    }

}