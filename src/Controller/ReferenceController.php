<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\GlpiReference;
use App\Entity\Reference;
use App\Form\ReferenceFormType;
use App\Repository\ReferenceRepository;
use App\Service\CaptchaValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReferenceController extends AbstractController
{
    #[Route('/reference', name: 'app_reference')]
    public function index(ReferenceRepository $referenceRepository, Request $request, EntityManagerInterface $manager, CaptchaValidator $captchaValidator): Response
    {
        $references = $referenceRepository->findBy([], ['created_at' => 'DESC']);
        $nb = count($references);

        $reference = new Reference();
        $glpi_reference = new GlpiReference();

        $captchaSiteKey     = $this->getParameter('captcha.site_key');

        $form = $this->createForm(ReferenceFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $success = false;

            $captcha_token = $request->request->get('captcha_token');
            if ($captcha_token !== null && $captchaValidator->validateToken($captcha_token)) {
                try {
                    $data = $form->getData();

                    $reference->setName($data['name']);
                    $reference->setUrl($data['url']);
                    $reference->setCountry(strtolower($data['country']));
                    $reference->setPhone($data['phone']);
                    $reference->setEmail($data['email']);
                    $reference->setReferent($data['referent']);
                    $reference->setComment($data['comment']);
                    $reference->setCreatedAt(new \DateTimeImmutable());
                    $glpi_reference->setNumAssets((int) $data['nb_assets']);
                    $glpi_reference->setNumHelpdesk((int) $data['nb_helpdesk']);
                    $glpi_reference->setReference($reference);
                    $glpi_reference->setCreatedAt(new \DateTimeImmutable());

                    $manager->persist($glpi_reference);
                    $manager->flush();

                    $success = true;
                } catch (\Throwable $e) {
                    $success = false;
                }
            }

            if ($success) {
                $this->addFlash('success', 'Your reference has been added successfully');
            } else {
                $this->addFlash('danger', 'An error occurred while adding your reference');
            }

            return $this->redirectToRoute('app_reference');

        }

        return $this->render(
            'reference/index.html.twig',
            [
                'references'   => $references,
                'nb_ref'       => $nb,
                'form'         => $form,
                'captchaSiteKey' => $captchaSiteKey
            ]
        );
    }
}
