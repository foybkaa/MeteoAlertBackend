<?php
namespace App\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class DestinataireService {
    private Connection $connection;

    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }

    /**
     * @throws Exception
     */
    public function findDestinatairesByInsee(string $insee): array {
        $sql = 'SELECT * FROM destinataires WHERE insee = :insee';

        $result = $this->connection->executeQuery(
            $sql,
            ['insee' => $insee]
        );

        return $result->fetchAllAssociative();
    }
}