<?php

namespace App\Form;

use App\Entity\Feature\StateAwareness;
use App\Entity\Consortium;
use App\Entity\Library;
use App\Entity\LibraryData;
use App\Entity\Organisation;
use App\Form\Type\AddressType;
use App\Form\Type\MailAddressType;
use App\Form\Type\StateChoiceType;
use App\Util\FormData;
use App\Util\LibraryTypes;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
                'choices' => new LibraryTypes(),
                'preferred_choices' => ['municipal'],
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
            ->add('library_system_name', ChoiceType::class, [
                'placeholder' => '-- Select --',
                'required' => false,
                'label' => 'Library System',
                'help' => 'Library system in use (Sierra, Axiell, Koha, etc.)',
                'choices' => [
                    'Abilita Kirjasto' => 'abilita',
                    'Axiell Aurora' => 'axiell_aurora',
                    'Axiell Arena' => 'axiell_arena',
                    'Axiell Micromarc' => 'axiell_micromarc',
                    'Axiell Origo' => 'axiell_origo',
                    'Axiell PallasPro' => 'axiell_pallaspro',
                    'Axiell Quria' => 'axiell_quria',
                    'Evergreen (os)' => 'evergreen',
                    'Ex Libris: Aleph' => 'ex_libris_aleph',
                    'Ex Libris: Alma' => 'ex_libris_alma',
                    'Ex Libris: Metalib' => 'ex_libris_metalib',
                    'Ex Libris: Voyager' => 'ex_libris_voyager',
                    'Infor V-Smart' => 'infor_vsmart',
                    'Innovative Interfaces: Sierra' => 'ii_sierra',
                    'Innovative Interfaces: Millenium' => 'ii_millenium',
                    'Innovative Interfaces: Polaris' => 'ii_polaris',
                    'Innovative Interfaces: VTLS-Virtua' => 'ii_vtls_virtua',
                    'Koha (os)' => 'koha',
                    'Oclc Worldshare' => 'oclc_worldshare',
                    'PrettyLib' => 'prettylib',
                    'SirsiDynix: Symphony' => 'sirsidynix_symphony',
                    'SirsiDynix: Horizon' => 'sirsidynix_horizon',
                ],
            ])
            ->add('library_system_servicepoint_id', null, [
                'required' => false,
                'label' => 'Library System Servicepoint Identifier',
                'help' => 'Identifier from the Library System (Sierra, Axiell, Koha, etc.)',
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
                'entry_options' => [
                    'data_class' => LibraryData::class,
                    'is_library_form' => is_a($options['data_class'], Library::class, true),

                ]
            ])

            ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $library = $event->getData();

            if ($library->isNew()) {
                $groups = $this->auth->getUser()->getGroup()->getTree();
            } else {
                $groups = $library->getGroup()->getTree();
            }

            if ($groups) {
                $event->getForm()->add('organisation', EntityType::class, [
                    'class' => Organisation::class,
                    'required' => false,
                    'label' => 'Parent organisation',
                    'placeholder' => '-- Select --',
                    'query_builder' => function ($repo) use ($groups) {
                        return $repo->createQueryBuilder('e')
                            ->join('e.translations', 'd')
                            ->orderBy('d.name')
                            ->andWhere('e.group IN (:groups)')
                            ->setParameter('groups', $groups)
                            ;
                    }
                ]);

                if (!$library->belongsToMunicipalConsortium()) {
                    $event->getForm()->add('consortium', EntityType::class, [
                        'class' => Consortium::class,
                        'label' => 'Consortium / Finna organisation',
                        'required' => false,
                        'placeholder' => '-- Automatic --',
                        'help' => 'Select only if this library is not a municipal library.',
                        'query_builder' => function ($repo) use ($groups) {
                            return $repo->createNonMunicipalConsortiumsQueryBuilder();
                        }
                    ]);
                } else {
                    $event->getForm()->add('consortium', HiddenType::class, [
                        'data' => null
                    ]);
                }
            }
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $library = $event->getData();
            $mailAddress = $library->getMailAddress();

            if (!count($mailAddress->getTranslations())) {
                $library->setMailAddress(null);
            }
        });

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if ($data->isNew()) {
                $langcode = $form->get('langcode')->getData();

                if ($address = $data->getAddress()) {
                    FormData::persistTemporaryTranslation($address->getTranslations(), $langcode);
                }

                if ($address = $data->getMailAddress()) {
                    FormData::persistTemporaryTranslation($address->getTranslations(), $langcode);
                }
            } else {
                // Rarely an existing library without an address is found. If
                // this is the case then the default language code needs to be
                // set from the library otherwise the address insertion will fail.
                $address = $data->getAddress();

                if($address && !$address->getDefaultLangcode()) {
                    $address->setDefaultLangcode($data->getDefaultLangcode());
                }

                $mail_address = $data->getMailAddress();

                if($mail_address && !$mail_address->getDefaultLangcode()) {
                    $mail_address->setDefaultLangcode($data->getDefaultLangcode());
                }
            }
        });
    }
}
