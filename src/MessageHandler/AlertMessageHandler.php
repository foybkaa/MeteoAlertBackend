<?php

namespace App\MessageHandler;

use App\Message\AlertMessage;
use App\Service\SmsService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class AlertMessageHandler
{
    private SmsService $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function __invoke(AlertMessage $message): void
    {
        $this->smsService->sendSms($message->getTelephone(), $message->getMessage());
    }
}