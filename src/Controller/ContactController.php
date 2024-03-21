<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\ContactFormType;
use App\Service\CaptchaValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(
        Request $request,
        MailerInterface $mailer,
        CaptchaValidator $captchaValidator,
        string $captchaSiteKey,
        string $contactFormRecipientEmail
    ): Response {
        $form = $this->createForm(ContactFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $success = false;

            $captcha_token = $request->request->get('captcha_token');
            if ($captcha_token !== null && $captchaValidator->validateToken((string) $captcha_token)) {
                try {
                    /**
                     * @var array{
                     *          Email: string,
                     *          Subject: string,
                     *          Message: string
                     *      } $contactFormData
                     */
                    $contactFormData = $form->getData();

                    $message = (new Email())
                        ->from($contactFormData['Email'])
                        ->to($contactFormRecipientEmail)
                        ->subject('New message from Telemetry: ' . $contactFormData['Subject'])
                        ->text($contactFormData['Message']);

                    $mailer->send($message);

                    $success = true;
                } catch (\Throwable $e) {
                    $success = false;
                }
            }

            if ($success) {
                $this->addFlash('success', 'Your message has been sent.');
            } else {
                $this->addFlash('danger', 'An error occurred while sending your message.');
            }

            return $this->redirectToRoute('app_contact');
        }

        return $this->render('contact/index.html.twig', [
            'form'           => $form->createView(),
            'captchaSiteKey' => $captchaSiteKey,
        ]);
    }
}
