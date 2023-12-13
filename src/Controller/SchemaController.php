<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SchemaController extends AbstractController
{
    #[Route('/schema/v1.json')]
    public function v1(string $schemaDir): Response
    {
        return $this->file($schemaDir . '/telemetry.v1.json');
    }
}
