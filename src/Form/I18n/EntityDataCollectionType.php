<?php

namespace App\Form\I18n;

use App\Util\SystemLanguages;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BaseType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntityDataCollectionType extends AbstractType
{
    private $currentLangcode;

    public function __construct(FormFactoryInterface $form_factory)
    {
        $this->formFactory = $form_factory;
    }

    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults([
            'default_langcode' => SystemLanguages::TEMPORARY_LANGCODE,
            'entry_type' => null,
            'entry_options' => [],
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($builder, $options) {
            $form_options = $event->getForm()->getParent()->getConfig()->getOptions();
            // $current_langcode = $form_options['current_langcode'] ?? $event->getForm()->getRoot()->getData()->getDefaultLangcode();

            if ($form_options['current_langcode']) {
                $current_langcode = $form_options['current_langcode'];
            // } elseif ($form_options['context_entity']) {
                // $current_langcode = $options['default_langcode'];
            } else {
                $current_langcode = $event->getForm()->getRoot()->getData()->getDefaultLangcode();
            }

            $this->currentLangcode = $current_langcode;

            $form = $event->getForm();
            $translations = $event->getData();

            $data_class = $form->getParent()->getConfig()->getOption('data_class') . 'Data';

            $entry_options = array_replace(
                ['data_class' => $data_class],
                $options['entry_options']
            );

            if (!$translations || $current_langcode == SystemLanguages::TEMPORARY_LANGCODE || !$translations->containsKey($current_langcode)) {
                $form->add($current_langcode, $options['entry_type'], [
                    'langcode' => $current_langcode,
                    'new_translation' => true,
                ] + $entry_options);
            }

            if ($translations) {
                foreach ($translations as $langcode => $_) {
                    $form->add($langcode, $options['entry_type'], [
                        'langcode' => $langcode,
                    ] + $entry_options);
                }
            }
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $translations = $event->getData();
            $entity = $event->getForm()->getParent()->getData();

            foreach ($translations as $key => $translation) {
                if ($translation) {
                    $translation->setLangcode($key);
                }

                if ($entity) {
                    $translation->setEntity($entity);
                }
            }
        });
    }

    public function finishView(FormView $view, FormInterface $form, array $options) : void
    {
        $view->setRendered(true);
        $parent = $view->parent;

        // NOTE: Used in the parent form to set content_language combobox value!
        $view->vars['current_langcode'] = $this->currentLangcode;

        foreach ($view->children as $langcode => $child) {
            foreach ($child as $name => $field) {
                if (!isset($parent->children[$name])) {
                    $fieldset = $this->formFactory->create(FormType::class, null, [
                        'csrf_protection' => false,
                        'label' => false,
                    ]);
                    $parent->children[$name] = $fieldset->createView();
                    $parent->children[$name]->vars['block_prefixes'][] = 'tr_group';
                    $parent->children[$name]->vars['unique_block_prefix'] = sprintf('%s_%s_tr_group', $parent->vars['unique_block_prefix'], $name);
                }

                $field->vars['block_prefixes'][] = sprintf('%s_%s', $parent->vars['unique_block_prefix'], $name);
                $field->vars['language_active'] = $langcode == $this->currentLangcode;
                $field->vars['langcode'] = $langcode;
                $field->parent = $parent->children[$name];
                $parent->children[$name]->children[$langcode] = $field;
            }
        }
    }
}
