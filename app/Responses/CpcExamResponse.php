<?php

namespace App\Responses;

use App\Core\Responses\AbstractResponse;

class CpcExamResponse extends AbstractResponse
{
    public function getCreateResponseMessage(): String
    {
        return "CPC exam created successfully";
    }

    public function getListResponseMessage(): String
    {
        return "List of CPC exams get successfully";
    }

    public function getUpdateResponseMessage(): String
    {
        return "CPC exam updated successfully";
    }

    public function getDeleteResponseMessage(): String
    {
        return "CPC exam deleted successfully";
    }

    public function getRecordResponseMessage(): String
    {
        return "CPC exam get successfully";
    }
}
