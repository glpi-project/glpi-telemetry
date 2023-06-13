<?php

namespace App\Controller;

use App\Entity\GlpiReference;
use App\Entity\Reference;
use App\Form\ReferenceFormType;
use App\Repository\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReferenceController extends AbstractController
{
    #[Route('/reference', name: 'app_reference')]
    public function index(ReferenceRepository $referenceRepository, Request $request, EntityManagerInterface $manager): Response
    {
        $references = $referenceRepository->findAll();
        $nb = count($references);

        $reference = new Reference;
        $glpi_reference = new GlpiReference();

        $form = $this->createForm(ReferenceFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();

            $reference->setName($data['name']);
            $reference->setUrl($data['url']);
            $reference->setCountry(strtolower($data['country']));
            $reference->setPhone($data['phone']);
            $reference->setEmail($data['email']);
            $reference->setReferent($data['referent']);
            $reference->setComment($data['comment']);
            $glpi_reference->setNumAssets($data['nb_assets']);
            $glpi_reference->setNumHelpdesk($data['nb_helpdesk']);
            $glpi_reference->setReference($reference);
            $manager->persist($glpi_reference);
            //$reference->setGlpiReference($glpi_reference);
            //$manager->persist($reference);
            $manager->flush();

            return $this->redirectToRoute('app_reference');

        }

        return $this->render(
            'reference/index.html.twig',
            [
                'references'   => $references,
                'nb_ref'       => $nb,
                'form'         => $form
            ]
        );
    }

}

