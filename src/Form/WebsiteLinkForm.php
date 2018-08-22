<?php

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

class WebsiteLinkForm extends ContactInfoForm
{
    public function form(FormBuilderInterface $builder, array $options) : void
    {
        parent::form($builder, $options);

        $builder->add('contact', UrlType::class, [
            'label' => 'Address'
        ]);
    }
}
