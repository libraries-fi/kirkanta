<?php

namespace App\Form;

use App\Entity\RegionData;
use App\Form\Type\RichtextType;
use Symfony\Component\Form\FormBuilderInterface;

class NotificationForm extends FormType
{
    public function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('subject')
            ->add('message', RichtextType::class)
            ;
    }
}
