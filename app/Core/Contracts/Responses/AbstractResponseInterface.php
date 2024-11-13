<?php

namespace App\Core\Contracts\Responses;

interface AbstractResponseInterface
{


    public function setResponse(string $responseType, int $code, string $message, array $data = []): void;

    public function setData(array $data);

    public function appendData($key, $value);

    public function getData(): array | null | string;

    public function getResponeType(): string;

    public function setCode(int $code): void;

    public function code(): int;

    public function toArray(): array;

    public function setMessage(string $message): void;

    public function message(): string;

    public function getCreateResponseMessage(): String;

    public function getListResponseMessage(): String;

    public function getUpdateResponseMessage(): String;

    public function getDeleteResponseMessage(): String;
}
