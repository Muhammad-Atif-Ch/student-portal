<?php

namespace App\Responses;

use App\Core\Responses\AbstractResponse;

class TechnicalDictionaryResponse extends AbstractResponse
{
    public function getCreateResponseMessage(): String
    {
        return "Technical dictionary term created successfully";
    }

    public function getListResponseMessage(): String
    {
        return "List of technical dictionary terms get successfully";
    }

    public function getUpdateResponseMessage(): String
    {
        return "Technical dictionary term updated successfully";
    }

    public function getDeleteResponseMessage(): String
    {
        return "Technical dictionary term deleted successfully";
    }

    public function getRecordResponseMessage(): String
    {
        return "Technical dictionary term get successfully";
    }
}
