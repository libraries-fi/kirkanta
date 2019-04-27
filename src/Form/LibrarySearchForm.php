<?php

namespace App\Form;

use App\Entity\UserGroup;
use App\Util\LibraryTypes;
use App\Util\OrganisationTypes;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class LibrarySearchForm extends SearchFormType
{
    protected function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('name')
            ->add('type', ChoiceType::class, [
                'placeholder' => '-- Any --',
                'choices' => new LibraryTypes()
            ])
            ->add('state', ChoiceType::class, [
                'placeholder' => '-- Any --',
                'choices' => [
                    'Published' => 1,
                    'Draft' => 0,
                ]
            ])
            ->add('group', EntityType::class, [
                'placeholder' => '-- Any --',
                'class' => UserGroup::class,
                // 'choice_label' => 'name'
            ])
            ;
    }
}
