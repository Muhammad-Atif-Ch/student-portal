<?php

namespace App\Responses;

use App\Core\Responses\AbstractResponse;

class CpcQuestionResponse extends AbstractResponse
{
    public function getCreateResponseMessage(): String
    {
        return "CPC question created successfully";
    }

    public function getListResponseMessage(): String
    {
        return "List of CPC questions get successfully";
    }

    public function getUpdateResponseMessage(): String
    {
        return "CPC question updated successfully";
    }

    public function getDeleteResponseMessage(): String
    {
        return "CPC question deleted successfully";
    }

    public function getRecordResponseMessage(): String
    {
        return "CPC question get successfully";
    }
}
