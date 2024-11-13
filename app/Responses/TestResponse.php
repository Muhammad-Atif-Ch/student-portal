<?php

namespace App\Responses;

use App\Core\Responses\AbstractResponse;

class TestResponse extends AbstractResponse
{
    public function getCreateResponseMessage() : String
    {
        return "Test created successfully";
    }

    public function getListResponseMessage() : String
    {
        return "List of tests get successfully";
    }

    public function getUpdateResponseMessage() : String
    {
        return "Test updated successfully";
    }

    public function getDeleteResponseMessage(): String
    {
        return "Test deleted successfully";
    }
    public function getRecordResponseMessage(): String
    {
        return "Test get successfully";
    }

}