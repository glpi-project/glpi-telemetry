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
    public function index(ReferenceRepository $referenceRepository, Request $request): Response
    {
        if ($request->query->get('showmodal') !== null) {
            // `showmodal` is the parameter passed by GLPI in the `Register your GLPI instance` link
            $uuid = $request->query->get('uuid');
            return $this->redirectToRoute('app_reference_register', ['uuid' => $uuid]);
        }

        $references = $referenceRepository->findBy([], ['created_at' => 'DESC']);

        return $this->render(
            'reference/index.html.twig',
            [
                'references' => $references,
            ]
        );
    }

    #[Route('/reference/register', name: 'app_reference_register')]
    public function register(
        Request $request,
        EntityManagerInterface $manager,
        CaptchaValidator $captchaValidator,
        string $captchaSiteKey
    ): Response {
        $form = $this->createForm(ReferenceFormType::class);
        if ($request->query->has('uuid')) {
            $form->setData(['uuid' => $request->query->get('uuid')]);
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $success = false;

            $captcha_token = $request->request->get('captcha_token');
            if ($captcha_token !== null && $captchaValidator->validateToken($captcha_token)) {
                try {
                    $data = $form->getData();

                    $reference = new Reference();
                    $reference->setUuid($data['uuid']);
                    $reference->setName($data['name']);
                    $reference->setUrl($data['url']);
                    $reference->setCountry($data['country'] !== null ? strtolower((string) $data['country']) : null);
                    $reference->setPhone($data['phone']);
                    $reference->setEmail($data['email']);
                    $reference->setReferent($data['referent']);
                    $reference->setComment($data['comment']);
                    $reference->setCreatedAt(new \DateTimeImmutable());

                    $glpiReference = new GlpiReference();
                    $glpiReference->setNumAssets($data['nb_assets']);
                    $glpiReference->setNumHelpdesk($data['nb_helpdesk']);
                    $glpiReference->setReference($reference);
                    $glpiReference->setCreatedAt(new \DateTimeImmutable());
                    $reference->setGlpiReference($glpiReference);

                    $manager->persist($reference);
                    $manager->flush();

                    $success = true;
                } catch (\Throwable $e) {
                    $success = false;
                }
            }

            if ($success) {
                $this->addFlash('success', 'Your reference has been added successfully');
                return $this->redirectToRoute('app_reference');
            } else {
                $this->addFlash('danger', 'An error occurred while adding your reference');
            }

        }
        return $this->render('reference/register.html.twig', [
            'form'  => $form,
            'captchaSiteKey' => $captchaSiteKey,
        ]);
    }
}
