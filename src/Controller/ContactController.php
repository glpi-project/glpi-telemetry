<?php

namespace App\Controller;

use App\Form\ContactFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, MailerInterface $mailer): Response
    {
        $captchaSiteKey     = $this->getParameter('captcha_site_key');
        $captchaSecretKey   = $this->getParameter(('captcha_secret_key'));

        $form = $this->createForm(ContactFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $contactFormData = $form->getData();

            $message = (new Email())
                ->from($contactFormData['Email'])
                ->to('mail@contact.fr')
                ->subject('New message from Telemetry'. $contactFormData['Subject'])
                ->text($contactFormData['Message']);

            $mailer->send($message);

            $this->addFlash('success', 'Your message has been sent');

            return $this->redirectToRoute('app_contact');
        }

        return $this->render('contact/index.html.twig', [
            'form'              => $form->createView(),
            'captchaSiteKey'    => $captchaSiteKey,
            'captchaSecretKey'  => $captchaSecretKey
        ]);
    }
}
