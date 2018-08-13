<?php

namespace App\Module\Translation\Form;

use App\Form\FormType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class TranslationForm extends FormType
{
    public function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder->add('search', SearchForm::class, [
            'label' => false,
            'attr' => [
                'class' => 'hidden-lg-down'
            ]
        ]);

        $builder->add('translations', CollectionType::class, [
            'entry_type' => TranslationItemType::class,
            'label' => false,
        ]);
    }

    public function finishView(FormView $view, FormInterface $form, array $options) : void
    {
        $view->children['search']->setRendered(true);
    }
}
