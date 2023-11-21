<?php

namespace App\Controller;

use App\Form\ContactFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, HttpClientInterface $client, MailerInterface $mailer): Response
    {
        $captchaSiteKey     = $this->getParameter('captcha.site_key');
        $captchaSecretKey   = $this->getParameter('captcha.secret_key');

        $form = $this->createForm(ContactFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $captcha_token = $request->request->get('captcha_token');

            if ($captcha_token === null) {
                $captcha_is_ok = false;
            } else {
                try {
                    $response = $client->request(
                        'POST',
                        'https://challenges.cloudflare.com/turnstile/v0/siteverify',
                        [
                            'headers' => [
                                'Content-Type' => 'application/json',
                            ],
                            'body' => [
                                'secret'   => $captchaSecretKey,
                                'response' => $request->request->get('captcha_token'),
                            ]
                        ]
                    );

                    $captcha_is_ok = $response->toArray()['success'] ?? false;
                } catch (\Throwable $e) {
                    // do not block contact form if there is a network issue while calling turnstile server
                    $captcha_is_ok = true;
                }
            }

            $success = false;

            if ($captcha_is_ok) {
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
                    // do not block contact form if there is a network issue while calling turnstile server
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
