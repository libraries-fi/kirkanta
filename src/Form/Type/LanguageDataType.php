<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LanguageDataType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults([
            'schema' => []
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        foreach ($options['schema'] as $input) {
            $field_options = [
                'translatable' => false,
                'translation_langcode' => $builder->getName(),
            ] + $input->getConfig()->getOptions();
            $builder->add($input->getName(), null, $field_options);
        }
    }
}
