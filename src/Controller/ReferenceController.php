<?php

namespace App\Controller;

use App\Entity\GlpiReference;
use App\Entity\Reference;
use App\Form\GlpiReferenceFormType;
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
        $references = $referenceRepository->getAllReferences();
        $nb = count($references);

        $form_ref = $this->createForm(ReferenceFormType::class);
        $form_glpiref = $this->createForm(GlpiReferenceFormType::class);

        $form_ref->handleRequest($request);
        $form_glpiref->handleRequest($request);

        $reference = new Reference();
        $data_ref = $form_ref->getData();
        $data_gref = $form_glpiref->getData();

        if ($form_ref->isSubmitted() && $form_ref->isValid()) {
            $reference = $data_ref;
        }

        $this->addFlash('sucess','your instance has been registered');
        // $form_ref->handleRequest($request);
        // $form_glpiref->handleRequest($request);

        // if ($form_ref->isSubmitted() && $form_ref->isValid()) {
        //         $data_rf = $form_ref->getData();
        //         $reference->setName($data_rf['name']);
        //         $reference->setUrl($data_rf['url']);
        //         $reference->setCountry($data_rf['country']);
        //         $reference->setPhone($data_rf['phone']);
        //         $reference->setEmail($data_rf['email']);
        //         $reference->setReferent($data_rf['referent']);
        //         $reference->setComment($data_rf['comment']);

        // }

        // if ($form_glpiref->isSubmitted() && $form_glpiref->isValid()) {
        //     $data_rfg = $form_glpiref->getData();
        //     $glpi_reference->setNumAssets($data_rfg['num_assets']);
        //     $glpi_reference->setNumHelpdesk($data_rfg['num_helpdesk']);
        // }

        //     print_r($reference);
        //     print_r($glpi_reference);
            // $manager->persist($reference);
            // $manager->persist($glpi_reference);
            // $manager->flush();

            // $this->addFlash('success', 'Your instance has been registered');

        return $this->render(
            'reference/index.html.twig',
            [
                'references'   => $references,
                'nb_ref'       => $nb,
                'form_ref'     => $form_ref,
                'form_glpiref' => $form_glpiref
            ]
        );
    }

}

