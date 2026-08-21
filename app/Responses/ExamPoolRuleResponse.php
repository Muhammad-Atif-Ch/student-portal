<?php

namespace App\Responses;

use App\Core\Responses\AbstractResponse;

class ExamPoolRuleResponse extends AbstractResponse
{
    public function getCreateResponseMessage() : String
    {
        return "Exam pool rule created successfully";
    }

    public function getListResponseMessage() : String
    {
        return "List of Exam Pool Rules get successfully";
    }

    public function getUpdateResponseMessage() : String
    {
        return "Exam pool rule updated successfully";
    }

    public function getDeleteResponseMessage(): String
    {
        return "Exam pool rule deleted successfully";
    }
    public function getRecordResponseMessage(): String
    {
        return "Exam pool rule get successfully";
    }

}
