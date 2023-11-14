<?php

namespace App\Form;

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
            ->add('name', TextType::class, ['label' => false, 'required' => true])
            ->add('url', UrlType::class, ['label' => false])
            ->add('country', CountryType::class, ['label' => false, 'required' => true])
            ->add('phone', TelType::class, ['label' => false])
            ->add('email', EmailType::class, ['label' => false, 'required' => true])
            ->add('referent', TextType::class, ['label' => false, 'required' => true])
            ->add('nb_assets', null, ['label' => false, 'required' => true])
            ->add('nb_helpdesk', null, ['label' => false, 'required' => true])
            ->add('comment', TextareaType::class, ['attr' => ['rows' => 6], 'label' => false])
        ;
    }
}
