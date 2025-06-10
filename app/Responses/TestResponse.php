<?php

namespace App\Responses;

use App\Core\Responses\AbstractResponse;

class TestResponse extends AbstractResponse
{
    public function getCreateResponseMessage() : String
    {
        return "Quiz created successfully";
    }

    public function getListResponseMessage() : String
    {
        return "List of Quizs get successfully";
    }

    public function getUpdateResponseMessage() : String
    {
        return "Quiz updated successfully";
    }

    public function getDeleteResponseMessage(): String
    {
        return "Quiz deleted successfully";
    }
    public function getRecordResponseMessage(): String
    {
        return "Quiz get successfully";
    }

}