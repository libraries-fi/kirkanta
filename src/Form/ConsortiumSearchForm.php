<?php

namespace App\Form;

use App\Entity\Library;
use App\Entity\UserGroup;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\CallbackTransformer;

class ConsortiumSearchForm extends SearchFormType
{
    protected function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('name')
            ->add('finna_extension', ChoiceType::class, [
                'label' => 'Shared to Finna',
                'placeholder' => '-- Any --',
                'choices' => [
                    'No' => 0,
                    'Yes' => 1,
                ]
            ])
            ;
    }
}
