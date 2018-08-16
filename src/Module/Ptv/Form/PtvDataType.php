<?php

namespace App\Module\Ptv\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class PtvDataType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder->add('enabled', CheckboxType::class, [
            'label' => 'Export this library to PTV',
            'false_values' => ['', '0'],
        ]);

        // $builder->add('published', ChoiceType::class, [
        //     'label' => 'State',
        //     'empty_data' => true,
        //     'choices' => [
        //         'Published' => true,
        //         'Draft' => false,
        //     ]
        // ]);

        $builder->add('ptv_identifier', null, [
            'required' => false,
            'label' => 'PTV identifier',
            'help' => 'Identifier used on PTV',
        ]);

        // $builder->add('')
    }
}
