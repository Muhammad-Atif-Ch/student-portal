<?php

namespace App\Responses;

use App\Core\Responses\AbstractResponse;

class TtsResponse extends AbstractResponse
{
    public function getCreateResponseMessage(): string
    {
        return 'Audio conversion process started successfully';
    }

    public function getListResponseMessage(): string
    {
        return 'List of audio conversions retrieved successfully';
    }

    public function getUpdateResponseMessage(): string
    {
        return 'Audio updated successfully';
    }

    public function getDeleteResponseMessage(): string
    {
        return 'Audio deleted successfully';
    }

    public function getStopResponseMessage(): string
    {
        return 'Audio conversion process stopped successfully';
    }

    public function getReconvertResponseMessage(): string
    {
        return 'Field audio regenerated successfully';
    }
}