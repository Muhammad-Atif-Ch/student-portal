<?php

namespace App\Responses;

use App\Core\Responses\AbstractResponse;

class ExamTypeResponse extends AbstractResponse
{
    public function getCreateResponseMessage() : String
    {
        return "Exam type created successfully";
    }

    public function getListResponseMessage() : String
    {
        return "List of Exam Types get successfully";
    }

    public function getUpdateResponseMessage() : String
    {
        return "Exam type updated successfully";
    }

    public function getDeleteResponseMessage(): String
    {
        return "Exam type deleted successfully";
    }
    public function getRecordResponseMessage(): String
    {
        return "Exam type get successfully";
    }

}