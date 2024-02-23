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
use Psr\Log\LoggerInterface;

class ReferenceController extends AbstractController
{
    private string $captchaSiteKey;

    private LoggerInterface $logger;

    public function __construct(string $captchaSiteKey, LoggerInterface $logger)
    {
        $this->captchaSiteKey = $captchaSiteKey;
        $this->logger = $logger;
    }

    #[Route('/reference', name: 'app_reference')]
    public function index(ReferenceRepository $referenceRepository, Request $request, EntityManagerInterface $manager, CaptchaValidator $captchaValidator): Response
    {

        if ($request->query->get('showmodal') !== null) {
            $uuid = $request->query->get('uuid');
            return $this->redirectToRoute('app_reference_register', ['uuid' => $uuid]);
        }

        $references = $referenceRepository->findBy([], ['created_at' => 'DESC']);
        $nb = count($references);

        return $this->render(
            'reference/index.html.twig',
            [
                'references'   => $references,
                'nb_ref'       => $nb,
            ]
        );
    }

    #[Route('/reference/register', name: 'app_reference_register')]

    public function registerReference(Request $request, EntityManagerInterface $manager, CaptchaValidator $captchaValidator): Response
    {
        $uuid = $request->query->get('uuid');

        $reference = new Reference();
        $glpi_reference = new GlpiReference();

        $form = $this->createForm(ReferenceFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->logger->debug('form submitted and valid');
            $success = false;

            $captcha_token = $request->request->get('captcha_token');
            if ($captcha_token !== null && $captchaValidator->validateToken($captcha_token)) {
                try {
                    $this->logger->debug('captcha token is valid');
                    $data = $form->getData();

                    $reference->setUuid($uuid);
                    $reference->setName($data['name']);
                    $reference->setUrl($data['url']);
                    $reference->setCountry($data['country'] !== null ? strtolower((string) $data['country']) : null);
                    $reference->setPhone($data['phone']);
                    $reference->setEmail($data['email']);
                    $reference->setReferent($data['referent']);
                    $reference->setComment($data['comment']);
                    $reference->setCreatedAt(new \DateTimeImmutable());
                    $glpi_reference->setNumAssets($data['nb_assets']);
                    $glpi_reference->setNumHelpdesk($data['nb_helpdesk']);
                    $glpi_reference->setReference($reference);
                    $glpi_reference->setCreatedAt(new \DateTimeImmutable());

                    $manager->persist($glpi_reference);
                    $manager->flush();

                    $success = true;
                } catch (\Throwable $e) {
                    $success = false;
                }
            } else {
                $this->logger->error('Captcha token is invalid');
                $this->addFlash('danger', 'Captcha token is invalid');
            }

            if ($success) {
                $this->addFlash('success', 'Your reference has been added successfully');
                return $this->redirectToRoute('app_reference');
            } else {
                $this->logger->error('An error occurred while adding your reference');
                $this->addFlash('danger', 'An error occurred while adding your reference');
            }

        }
        return $this->render('reference/register.html.twig', [
            'form'  => $form,
            'captchaSiteKey' => $this->captchaSiteKey
        ]);
    }
}
