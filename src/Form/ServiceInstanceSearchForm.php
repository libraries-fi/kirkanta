<?php

namespace App\Form;

use App\Entity\Library;
use App\Entity\UserGroup;
use App\Util\ServiceTypes;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class ServiceInstanceSearchForm extends SearchFormType
{
    protected function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('name')
            ->add('type', ChoiceType::class, [
                'placeholder' => '-- Any --',
                'choices' => new ServiceTypes
            ]);
    }
}
