<?php

namespace App\Form;

use App\Entity\Library;
use App\Entity\UserGroup;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class UserGroupSearchForm extends SearchFormType
{
    protected function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('name');
    }
}
