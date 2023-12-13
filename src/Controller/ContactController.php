<?php

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
    public function index(Request $request, MailerInterface $mailer, CaptchaValidator $captchaValidator): Response
    {
        $captchaSiteKey     = $this->getParameter('captcha.site_key');

        $form = $this->createForm(ContactFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $success = false;

            $captcha_token = $request->request->get('captcha_token');
            if ($captcha_token !== null && $captchaValidator->validateToken($captcha_token)) {
                try {
                    $contactFormData = $form->getData();

                    $message = (new Email())
                        ->from($contactFormData['Email'])
                        ->to($this->getParameter('contact_form.recipient.email'))
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
