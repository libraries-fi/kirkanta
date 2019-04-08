<?php

namespace App\Form;

use App\Entity\Feature\GroupOwnership;
use App\Entity\UserGroup;
use App\EntityTypeManager;
use App\Util\SystemLanguages;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
            'disable_ownership' => false,
        ]);
    }

    public function form(FormBuilderInterface $builder, array $options) : void
    {
        if (!$options['disable_ownership']) {
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                $entity = $event->getData();
                $form = $event->getForm();

                if (!$form->getParent() && $entity instanceof GroupOwnership) {
                    $help = 'Changing this value will change user permissions for this record.';

                    if ($this->auth->isGranted('foobar')) {
                        $owner = $entity->getOwner() ?? $this->auth->getUser()->getGroup();
                        $preferred_groups = $owner->getTree();

                        $form->add('owner', EntityType::class, [
                            'help' => $help,
                            'class' => UserGroup::class,
                            'query_builder' => function($repo) {
                                return $repo->createQueryBuilder('e')
                                    ->orderBy('e.parent');
                            },
                            'preferred_choices' => $preferred_groups
                        ]);

                        $event->getData()->setOwner($owner);
                    } else {
                        $group = $this->auth->getUser()->getGroup();
                        do {
                            $choices[] = $group;
                        } while ($group = $group->getParent());

                        $form->add('owner', EntityType::class, [
                            'help' => $help,
                            'class' => UserGroup::class,
                            'choices' => $choices,
                            'attr' => [
                                'data-no-sort' => true
                            ]
                        ]);
                    }
                }
            });
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use($options) {
            if ($event->getData()->isNew()) {
                $languages = $this->languages->getData();

                if ($options['context_entity']) {
                    $enabled = $options['context_entity']->getTranslations()->getKeys();
                    $languages = array_intersect($languages, $enabled);
                }

                $event->getForm()->add('langcode', ChoiceType::class, [
                    'label' => 'Language',
                    'placeholder' => '-- Select --',
                    'mapped' => false,
                    'choices' => $languages,
                    'help' => 'Default language for this record.',
                    'preferred_choices' => ['fi', 'sv'],
                    'attr' => [
                        // Slugger (in JS) uses this attribute to get default langcode.
                        'data-default-langcode' => true
                    ]
                ]);
            }
        });

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
