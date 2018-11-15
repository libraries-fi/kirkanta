<?php

namespace App\Form;

use App\EntityTypeManager;
use App\Util\SystemLanguages;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

abstract class EntityFormType extends FormType
{
    protected $types;

    public function __construct(RequestStack $request_stack, Security $auth, EntityTypeManager $types, SystemLanguages $languages)
    {
        parent::__construct($request_stack, $auth);
        $this->types = $types;
        $this->languages = $languages;
    }

    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        $options->setDefaults([
            // e.g. parent entity like Library or something.
            'context_entity' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        parent::buildForm($builder, $options);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            if ($event->getData()->isNew()) {
                $event->getForm()->add('langcode', ChoiceType::class, [
                    'label' => 'Language',
                    'placeholder' => '-- Select --',
                    'mapped' => false,
                    'choices' => $this->languages
                ]);
            }
        });

        // $builder->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
        //     $data = $event->getData();
        //
        //     if (isset($data['translations'], $data['langcode'])) {
        //         $langcode = $data['langcode'];
        //         $translations = $data['translations'];
        //         $tl = SystemLanguages::TEMPORARY_LANGCODE;
        //
        //         if (isset($translations[$tl])) {
        //             $data['translations'] = [$langcode => $translations[$tl]];
        //             $event->setData($data);
        //         }
        //     }
        // });

        $builder->addEventListener(FormEvents::SUBMIT, function(FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if ($data->isNew() && $form->has('translations')) {
                $translations = $data->getTranslations();
                $langcode = $form->get('langcode')->getData();
                $tmplang = SystemLanguages::TEMPORARY_LANGCODE;

                if (isset($translations[$tmplang])) {
                    $translations[$langcode] = $translations[$tmplang];
                    $translations[$langcode]->setLangcode($langcode);
                    unset($translations[$tmplang]);

                    $data->setDefaultLangcode($langcode);
                }

                $data->setTranslations($translations);
            }
        });
    }
}
