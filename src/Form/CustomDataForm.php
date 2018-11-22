<?php

namespace App\Form;

use App\Entity\Consortium;
use App\Entity\Region;
use App\Entity\RegionalLibrary;
use App\Util\PersonQualities;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;

class CustomDataForm extends FormType
{
    public function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('title', null, [
                'help' => 'Informative name for users.'
            ])
            ->add('id', null, [
                'help' => 'Machine-readable identifier.',
            ])
            ->add('value', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class, [
                'attr' => [
                    'rows' => 4
                ]
            ])
            ;
    }
}
