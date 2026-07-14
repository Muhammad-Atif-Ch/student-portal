<?php

namespace App\Responses;

use App\Core\Responses\AbstractResponse;

class TranslationResponse extends AbstractResponse
{
    public function getCreateResponseMessage() : String
    {
        return "Translation created successfully";
    }

    public function getListResponseMessage() : String
    {
        return "List of Translations get successfully";
    }

    public function getUpdateResponseMessage() : String
    {
        return "Translation updated successfully";
    }

    public function getDeleteResponseMessage(): String
    {
        return "Translation deleted successfully";
    }
    public function getRecordResponseMessage(): String
    {
        return "Translation get successfully";
    }

}