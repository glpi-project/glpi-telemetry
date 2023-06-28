<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class PostgresConnection
{
    private $connection;
    public function __construct(private EntityManagerInterface $entityManager) {
    }

    public function getPostGresConnection(): string
    {
        $this->connection = $this->entityManager->getConnection();
        try {
            $this->connection;
            return "Connection to PostGres OK";
        } catch (\Exception $e) {
            $errmsg = $e->getMessage();
            return $errmsg;
        }
    }
}


