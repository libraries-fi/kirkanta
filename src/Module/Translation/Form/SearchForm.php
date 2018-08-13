<?php

namespace App\Module\Translation\Form;

use App\Form\SearchFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SearchForm extends SearchFormType
{
    public function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder->add('locale', ChoiceType::class, [
            'choices' => [
                // 'English' => 'en',
                'Finnish' => 'fi',
                'Swedish' => 'sv',
            ],
            'empty_data' => 'fi'
        ]);

        $builder->add('text', null, [

        ]);

        $builder->add('only_null', CheckboxType::class, [
            'label' => 'Untranslated only'
        ]);
    }
}
