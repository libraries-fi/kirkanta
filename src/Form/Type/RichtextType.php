<?php

namespace App\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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

    public function getBlockPrefix() : string
    {
        return 'richtext';
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (empty($view->vars['attr']['class'])) {
            $view->vars['attr']['class'] = 'richtext';
        } else {
            $view->vars['attr']['class'] .= ' richtext';
        }
    }
}
