<?php

namespace App\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RichtextType extends TextareaType
{
    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        $options->setDefaults([
            'attr' => [
                'rows' => 10,
            ]
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder->addModelTransformer(new CallbackTransformer(
            function($db_value) {
                return $db_value;
            },
            function($user_input) {
                if ($user_input == '<p>&nbsp;</p>' || $user_input == '') {
                    return null;
                } else {
                    return $user_input;
                }
            }
        ));
    }

    public function getBlockPrefix() : string
    {
        return 'richtext';
    }
}
