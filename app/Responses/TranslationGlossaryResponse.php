<?php

namespace App\Responses;

use App\Core\Responses\AbstractResponse;

class TranslationGlossaryResponse extends AbstractResponse
{
    public function getCreateResponseMessage(): string
    {
        return 'Translation Glossary Create successfully';
    }

    public function getListResponseMessage(): string
    {
        return 'List of Translations Glossary get successfully';
    }

    public function getUpdateResponseMessage(): string
    {
        return 'Translation Glossary updated successfully';
    }

    public function getDeleteResponseMessage(): string
    {
        return 'Translation Glossary deleted successfully';
    }

    public function getImportResponseMessage(): string
    {
        return 'Translation Glossary import successfully';
    }
}
