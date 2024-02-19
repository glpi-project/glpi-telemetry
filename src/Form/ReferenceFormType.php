<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

class ReferenceFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => false, 'required' => true, 'attr' => ['placeholder' => 'Your (company) name']])
            ->add('url', UrlType::class, ['label' => false, 'attr' => ['placeholder' => 'URL']])
            ->add('country', CountryType::class, ['label' => false, 'placeholder' => 'Choose a country'])
            ->add('phone', TelType::class, ['label' => false, 'attr' => ['placeholder' => 'Phone']])
            ->add('email', EmailType::class, ['label' => false, 'attr' => ['placeholder' => 'Email']])
            ->add('referent', TextType::class, ['label' => false, 'attr' => ['placeholder' => 'Referent name']])
            ->add('nb_assets', NumberType::class, ['label' => false, 'attr' => ['placeholder' => 'Number of assets', 'step' => 1]])
            ->add('nb_helpdesk', NumberType::class, ['label' => false, 'attr' => ['placeholder' => 'Number of helpdesk', 'step' => 1]])
            ->add('comment', TextareaType::class, ['label' => false, 'attr' => ['rows' => 6, 'placeholder' => 'Your message']])
        ;
    }
}
