<?php

namespace App\Form\I18n;

use App\Util\SystemLanguages;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentLanguageChoiceType extends ChoiceType
{
    public function configureOptions(OptionsResolver $resolver) : void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'label' => 'Language',
            'required' => false,
            'enabled_languages' => [SystemLanguages::DEFAULT_LANGCODE],
            'preferred_choices' => ['-- All --'],

            // NOTE: Entities do not contain this property but it shouldn't cause problems
            // because usually this field is inserted manually in the code with a default value.
            // 'mapped' => false,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $languages = (new SystemLanguages)->getData();
        $languages = array_intersect($languages, $options['enabled_languages']);
        $options['choices'] = ['-- All --' => ''] + $languages;

        parent::buildForm($builder, $options);
    }
}
