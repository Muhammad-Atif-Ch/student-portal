<?php

namespace App\Responses;

use App\Core\Responses\AbstractResponse;

class LanguageResponse extends AbstractResponse
{
    public function getCreateResponseMessage(): string
    {
        return "Language created successfully";
    }

    public function getListResponseMessage(): string
    {
        return "List of Languages get successfully";
    }

    public function getUpdateResponseMessage(): string
    {
        return "Language updated successfully";
    }

    public function getDeleteResponseMessage(): string
    {
        return "Language deleted successfully";
    }
    public function getRecordResponseMessage(): string
    {
        return "Language get successfully";
    }

}