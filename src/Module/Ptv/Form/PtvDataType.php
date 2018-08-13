<?php

namespace App\Module\Ptv\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class PtvDataType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder->add('enabled', CheckboxType::class, [
            'label' => 'Export this library to PTV',
        ]);

        $builder->add('published', ChoiceType::class, [
            'label' => 'State',
            'choices' => [
                'Published' => true,
                'Draft' => false,
            ]
        ]);

        $builder->add('ptv_identifier', null, [
            'required' => false,
            'label' => 'PTV identifier',
            'help' => 'Identifier used on PTV',
        ]);
    }
}
