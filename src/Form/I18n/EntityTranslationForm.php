<?php

namespace App\Form\I18n;

use App\Form\FormType;
use App\Util\SystemLanguages;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntityTranslationForm extends FormType
{
    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults([
            'existing_translations' => []
        ]);
    }

    public function form(FormBuilderInterface $builder, array $options) : void
    {
        $languages = (new SystemLanguages())->getData();
        $available = array_diff($languages, $options['existing_translations']);

        $builder->add('langcode', ChoiceType::class, [
            'choices' => $available,
            'placeholder' => '- Select -',
        ]);
    }
}
