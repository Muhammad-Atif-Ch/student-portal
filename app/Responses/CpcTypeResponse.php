<?php

namespace App\Responses;

use App\Core\Responses\AbstractResponse;

class CpcTypeResponse extends AbstractResponse
{
    public function getCreateResponseMessage(): String
    {
        return "CPC type created successfully";
    }

    public function getListResponseMessage(): String
    {
        return "List of CPC types get successfully";
    }

    public function getUpdateResponseMessage(): String
    {
        return "CPC type updated successfully";
    }

    public function getDeleteResponseMessage(): String
    {
        return "CPC type deleted successfully";
    }

    public function getRecordResponseMessage(): String
    {
        return "CPC type get successfully";
    }
}
