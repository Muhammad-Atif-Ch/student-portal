<?php

namespace App\Responses;

use App\Core\Responses\AbstractResponse;

class QuestionResponse extends AbstractResponse
{
    public function getCreateResponseMessage() : String
    {
        return "Question created successfully";
    }

    public function getListResponseMessage() : String
    {
        return "List of Questions get successfully";
    }

    public function getUpdateResponseMessage() : String
    {
        return "Question updated successfully";
    }

    public function getDeleteResponseMessage(): String
    {
        return "Question deleted successfully";
    }
    public function getRecordResponseMessage(): String
    {
        return "Question get successfully";
    }

}