<?php

namespace App\Form;

use App\Entity\Feature\StateAwareness;
use App\Entity\Library;
use App\Entity\Organisation;
use App\Form\Type\AddressType;
use App\Form\Type\MailAddressType;
use App\Form\Type\StateChoiceType;
use App\Util\LibraryTypes;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LibraryForm extends EntityFormType
{
    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        $options->setDefaults([
            'data_class' => Library::class,
        ]);
    }

    public function form(FormBuilderInterface $builder, array $options) : void
    {
        parent::form($builder, $options);

        $builder
            ->add('state', StateChoiceType::class)
            ->add('type', ChoiceType::class, [
                'placeholder' => '-- Select --',
                'choices' => new LibraryTypes
            ])
            ->add('main_library', CheckboxType::class, [
                'required' => false,
            ])
            // ->add('organisation', EntityType::class, [
            //     'property_path' => 'organisation',
            //     'label' => 'Parent organisation',
            //     'required' => false,
            //     'class' => Organisation::class,
            //     'placeholder' => '-- Select --',
            //     'choice_label' => 'name',
            // ])
            ->add('isil', null, [
                'required' => false,
                'label' => 'ISIL',
            ])
            ->add('identificator', null, [
                'required' => false,
                'label' => 'Official identifier',
            ])
            ->add('address', AddressType::class, [
                'current_langcode' => $options['current_langcode'],
            ])
            ->add('mail_address', MailAddressType::class, [
                'required' => false,
                'current_langcode' => $options['current_langcode'],
            ])
            ->add('trains', null, [
                'required' => false,
                'attr' => [
                    'rows' => 4
                ]
            ])
            ->add('buses', null, [
                'required' => false,
                'attr' => [
                    'rows' => 4
                ]
            ])
            ->add('trams', null, [
                'required' => false,
                'attr' => [
                    'rows' => 4
                ]
            ])
            ->add('construction_year', IntegerType::class, [
                'required' => false
            ])
            ->add('interior_designer', null, [
                'required' => false
            ])
            ->add('building_architect', null, [
                'required' => false
            ])
            ->add('translations', I18n\EntityDataCollectionType::class, [
                'entry_type' => EntityData\LibraryDataType::class,
            ])

            ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use($options) {
            $library = $event->getData();

            if ($library instanceof Library) {
                $groups = $library->getGroup()->getTree();
            } else {
                $groups = $this->auth->getUser()->getGroup()->getTree();
            }

            if ($groups) {
                $event->getForm()->add('organisation', EntityType::class, [
                    'class' => Organisation::class,
                    'required' => false,
                    'label' => 'Parent organisation',
                    'placeholder' => '-- Select --',
                    'query_builder' => function($repo) use($groups) {
                        return $repo->createQueryBuilder('e')
                            ->join('e.translations', 'd')
                            ->orderBy('d.name')
                            ->andWhere('e.group IN (:groups)')
                            ->setParameter('groups', $groups)
                            ;
                    }
                ]);
            }
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event) {
            $library = $event->getData();
            $mailAddress = $library->getMailAddress();

            if (!count($mailAddress->getTranslations())) {
                $library->setMailAddress(null);
            }
        });
    }
}
