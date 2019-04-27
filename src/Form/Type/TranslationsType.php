<?php

namespace App\Form\Type;

use App\I18n\Translations;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TranslationsType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults([
            'data_class' => Translations::class,
            'translation_languages' => [],
            'translation_default_langcode' => Translations::DEFAULT_LANGCODE,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($builder, $options) {
            $tr_fields = [];

            foreach ($event->getForm()->getParent() as $field) {
                if ($field->getConfig()->getOption('translatable')) {
                    $tr_fields[] = $field;
                }
            }

            if ($event->getData() instanceof Translations) {
                $languages = $event->getData()->getLanguages();
            } else {
                $languages = $options['translation_languages'];
                $languages[] = $options['translation_default_langcode'];
            }

            foreach ($languages as $langcode) {
                $event->getForm()->add($langcode, LanguageDataType::class, [
                    'label' => $langcode,
                    'schema' => $tr_fields,
                ]);
            }
        });

        // $builder->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) use($builder, $options) {
        //     // var_dump($event);
        //     $form = $event->getForm()->getParent();
        //     $data = $event->getData()[$options['translation_default_langcode']];
        //
        //     foreach ($data as $name => $value) {
        //         $form->get($name)->setData($value);
        //     }
        //     // exit;
        // });
    }

    public function finishView(FormView $view, FormInterface $form, array $options) : void
    {
        $view->setRendered(true);
        $parent = $view->parent->children;

        foreach ($view->children as $langcode => $group) {
            foreach ($group->children as $name => $tr_field) {
                if (isset($parent[$name])) {
                    $parent[$name]->vars['translation_fields'][$langcode] = $tr_field;
                }
            }
        }
    }
}
