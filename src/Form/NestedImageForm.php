<?php

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NestedImageForm extends FormType
{
    public function form(FormBuilderInterface $builder, array $options) : void {
        $builder
            ->add('image', Type\NestedImageType::class, [
                'data_class' => $options['data_class'],
            ])
            ;

        $builder->setDataMapper(new DataMapper\ParentToChildMapper());
    }

    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        $options->setDefaults([
            'data_class' => null,
        ]);
    }
}
