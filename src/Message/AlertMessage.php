<?php

namespace App\Message;

class AlertMessage
{
    private string $telephone;
    private string $message;

    public function __construct(string $telephone, string $message)
    {
        $this->telephone = $telephone;
        $this->message = $message;
    }

    public function getTelephone(): string
    {
        return $this->telephone;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
