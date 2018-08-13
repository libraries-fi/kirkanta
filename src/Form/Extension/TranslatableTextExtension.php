<?php

namespace App\Form\Extension;

use LogicException;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TranslatableTextExtension extends AbstractTypeExtension
{
    public function getExtendedType() : string
    {
        return TextType::class;
    }

    public function configureOptions(OptionsResolver $options) : void
    {
        $options->setDefaults([
            'langcode' => null,
        ]);
    }

    public function buildView(FormView $view, FormInterface $input, array $options) : void
    {
        if ($options['langcode']) {
            $view->vars['langcode'] = $options['langcode'];
        }
    }
}
