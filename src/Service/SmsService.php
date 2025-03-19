<?php

namespace App\Service;

use Psr\Log\LoggerInterface;

class SmsService
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function sendSms(string $phoneNumber, string $message): void
    {
        $logMessage = sprintf(
            "Envoi de l'alerte à %s : %s (Date : %s)",
            $phoneNumber,
            $message,
            (new \DateTime())->format('d-m-Y H:i:s')
        );

        $this->logger->info($logMessage);
    }
}
