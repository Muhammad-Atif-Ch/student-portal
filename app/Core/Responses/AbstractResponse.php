<?php

namespace App\Core\Responses;

use App\Core\Contracts\Responses\AbstractResponseInterface;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class AbstractResponse implements AbstractResponseInterface
{

    /**
     * data.
     *
     * @var array
     */
    protected $data;

    /**
     * code.
     *
     * @var int
     */
    protected $code = 200;


    /**
     * message.
     *
     * @var string
     */
    protected $message = 200;


    /**
     * responseType.
     *
     * @var string
     */
    protected $responseType;

    /**
     * Set Response.
     *
     * @param array $data
     * @param string $message
     * @param int $code
     * @return mixed|void
     */
    public function setResponse(string $responseType, int $code, string $message, $data = null): void
    {
        $this->responseType = $responseType;
        $this->data = $data;
        $this->message = $message;
        $this->code = $code;
    }

    /**
     * Set Data.
     *
     * @param array $data
     * @return mixed|void
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * appendData.
     * @param $key
     * @param $value
     */
    public function appendData($key, $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * getData.
     * @return array
     */
    public function getData(): array | null | string
    {
        return $this->data;
    }

    /**
     * setCode.
     * @param int $code
     */
    public function setCode(int $code): void
    {
        $this->code = $code;
    }

    /**
     * code.
     * @return int
     */
    public function code(): int
    {
        return $this->code;
    }

    /**
     * toArray.
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }


    /**
     * setMessage.
     * @return void
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * message.
     * @return string
     */
    public function message(): string
    {
        return $this->message;
    }

    /**
     * getResponeType
     * @return string
     */
    public function getResponeType(): string
    {
        return $this->responseType;
    }
}
