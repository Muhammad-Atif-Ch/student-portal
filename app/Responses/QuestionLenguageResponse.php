<?php

namespace App\Responses;

use App\Core\Responses\AbstractResponse;

class QuestionLenguageResponse extends AbstractResponse
{
    public function getCreateResponseMessage(): string
    {
        return "Question Lenguage created successfully";
    }

    public function getListResponseMessage(): string
    {
        return "List of Questions Lenguages get successfully";
    }

    public function getUpdateResponseMessage(): string
    {
        return "Question Lenguage updated successfully";
    }

    public function getDeleteResponseMessage(): string
    {
        return "Question Lenguage deleted successfully";
    }
    public function getRecordResponseMessage(): string
    {
        return "Question Lenguage get successfully";
    }

}