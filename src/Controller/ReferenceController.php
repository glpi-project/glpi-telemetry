<?php

namespace App\Controller;

use App\Repository\ReferenceRepository;
use Rinvex\Country\Country;
use Rinvex\Country\CountryLoader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReferenceController extends AbstractController
{
    #[Route('/reference', name: 'app_reference')]
    public function index(ReferenceRepository $referenceRepository): Response
    {
        $references = $referenceRepository->getAllReferences();

        return $this->render(
            'reference/index.html.twig',
            [
                'references' => $references,
            ]
        );
    }

}

