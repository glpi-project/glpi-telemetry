<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Service\RefreshPhpVersionCache;
class PhpVersionController extends AbstractController
{
    public $phpVersionData;

    #[Route('/php/version', name: 'app_php_version')]


    public function index(Request $request, RefreshPhpVersionCache $refreshPhpVersionCache): JsonResponse
    {
        $startDate      = $request->query->get('startDate');
        $endDate        = $request->query->get('endDate');
        $filter         = $request->query->get('filter');
        $forceUpdate    = false;

        $result = $refreshPhpVersionCache->refreshCache($startDate, $endDate, $filter, $forceUpdate);

        return $this->json($result);
    }

}

