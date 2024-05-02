<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ContactFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, ['label' => false, 'required' => true])
            ->add('subject', TextType::class, ['label' => false, 'required' => true])
            ->add('message', TextareaType::class, [
                'label' => false,
                'attr' => ['rows' => 6],
                'required' => true,
            ])
        ;
    }
}
