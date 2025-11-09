<?php

namespace App\Utils;

use JsonSerializable;

class OperationResult implements JsonSerializable
{
    private ?string $status;
    private ?int $statusCode;
    private ?string $message;
    private ?array $data = null;

    public function __construct(string $status, int $statusCode, string $message, ?array $data)
    {
        $this->status = $status;
        $this->statusCode = $statusCode;
        $this->message = $message;
        $this->data = $data;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'status' => $this->status,
            'statusCode' => $this->statusCode,
            'message' => $this->message,
            'data' => $this->data,
        ];
    }


}
