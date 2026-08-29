<?php

namespace App\Responses;

use App\Core\Responses\AbstractResponse;

class CpcCaseStudyResponse extends AbstractResponse
{
    public function getCreateResponseMessage(): String
    {
        return "CPC case study created successfully";
    }

    public function getListResponseMessage(): String
    {
        return "List of CPC case studies get successfully";
    }

    public function getUpdateResponseMessage(): String
    {
        return "CPC case study updated successfully";
    }

    public function getDeleteResponseMessage(): String
    {
        return "CPC case study deleted successfully";
    }

    public function getRecordResponseMessage(): String
    {
        return "CPC case study get successfully";
    }
}
