<?php

namespace App\Responses;

use App\Core\Responses\AbstractResponse;

class QuestionLanguageResponse extends AbstractResponse
{
    public function getCreateResponseMessage(): string
    {
        return "Question Language created successfully";
    }

    public function getListResponseMessage(): string
    {
        return "List of Questions Languages get successfully";
    }

    public function getUpdateResponseMessage(): string
    {
        return "Question Language updated successfully";
    }

    public function getDeleteResponseMessage(): string
    {
        return "Question Language deleted successfully";
    }
    public function getRecordResponseMessage(): string
    {
        return "Question Language get successfully";
    }

}