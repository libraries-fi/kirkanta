<?php

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WebsiteLinkForm extends ContactInfoForm
{
    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        
        $options->setDefaults([
            'data_class' => \App\Entity\WebsiteLink::class,
        ]);
    }

    public function form(FormBuilderInterface $builder, array $options) : void
    {
        parent::form($builder, $options);

        $builder->add('contact', UrlType::class, [
            'label' => 'URL'
        ]);
    }
}
