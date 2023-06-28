<?php

namespace App\Service;

use App\Repository\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;

class PostgresConnection
{
    private $connection;
    private $data;
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ReferenceRepository $referenceRepository
        )
    {
        $this->connection = $this->entityManager->getConnection();
        $this->data = $referenceRepository->findBy(array (), array('id' => 'ASC'), 20, 0);
    }

    public function getPostGresConnection(): string
    {

        try {
            $this->connection;
            return "Connection to PostGres OK";
        } catch (\Exception $e) {
            $errmsg = $e->getMessage();
            return $errmsg;
        }
    }

    public function getPostgresData()
    {
        try {
            $this->data;
            return $this->data;
        } catch (\Exception $e) {
            $errmsg = $e->getMessage();
            return $errmsg;
        }
    }
}


