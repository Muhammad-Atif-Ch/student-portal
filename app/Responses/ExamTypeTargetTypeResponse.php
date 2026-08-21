<?php

namespace App\Responses;

use App\Core\Responses\AbstractResponse;

class ExamTypeTargetTypeResponse extends AbstractResponse
{
    public function getCreateResponseMessage() : String
    {
        return "Exam type target type created successfully";
    }

    public function getListResponseMessage() : String
    {
        return "List of Exam Type Target Types get successfully";
    }

    public function getUpdateResponseMessage() : String
    {
        return "Exam type target type updated successfully";
    }

    public function getDeleteResponseMessage(): String
    {
        return "Exam type target type deleted successfully";
    }
    public function getRecordResponseMessage(): String
    {
        return "Exam type target type get successfully";
    }

}
