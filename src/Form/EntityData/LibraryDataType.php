<?php

namespace App\Form\EntityData;

use App\Entity\EmailAddress;
use App\Entity\Library;
use App\Entity\LibraryData;
use App\Entity\PhoneNumber;
use App\Entity\ServicePoint;
use App\Entity\WebsiteLink;
use App\Form\I18n\EntityDataType;
use App\Form\Type\RichtextType;
use App\Form\Type\SlugType;
use App\Util\SystemLanguages;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LibraryDataType extends EntityDataType
{
    protected $dataClass = LibraryData::class;

    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('name', null, [
                'label' => 'Name',
            ])
            ->add('short_name', null, [
                'required' => false,
                'label' => 'Short name',
            ])
            ->add('slug', SlugType::class, [
                'label' => 'Slug',
                'entity_type' => 'library',
                'langcode' => $options['langcode'],
            ])
            ->add('slogan', null, [
                'required' => true,
                'label' => 'Slogan',
            ])
            ->add('description', RichtextType::class, [
                'required' => false,
                'label' => 'Description',
            ])
            ->add('transit_directions', TextareaType::class, [
                'required' => false,
                'label' => 'Transit directions',
                'attr' => [
                    'rows' => 4
                ]
            ])
            ->add('parking_instructions', TextareaType::class, [
                'required' => false,
                'label' => 'Parking instructions',
                'attr' => [
                    'rows' => 4
                ]
            ])
            ->add('building_name', null, [
                'label' => 'Building name',
                'required' => false,
            ])
            ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use($options) {
            $data = $event->getData();

            $qb = function($repo) use($data) {
                // var_dump($repo);
                // exit('ok');
                return $repo->createQueryBuilder('e')
                    ->addSelect('d')
                    ->join('e.translations', 'd', 'WITH', 'd.langcode = :langcode')
                    ->andWhere('e.parent = :library')
                    ->orderBy('d.name')
                    ->setParameter('library', $data->getEntity())
                    ->setParameter('langcode', SystemLanguages::DEFAULT_LANGCODE)
                    ;
            };

            if ($data instanceof LibraryData) {
                $event->getForm()
                    ->add('email', EntityType::class, [
                        'label' => 'Email address',
                        'class' => EmailAddress::class,
                        'required' => false,
                        'placeholder' => '-- Select --',
                        'query_builder' => $qb,
                    ])
                    ->add('homepage', EntityType::class, [
                        'label' => 'Homepage',
                        'class' => WebsiteLink::class,
                        'required' => false,
                        'placeholder' => '-- Select --',
                        'query_builder' => $qb,
                    ])
                    ->add('phone', EntityType::class, [
                        'label' => 'Phone number',
                        'class' => PhoneNumber::class,
                        'required' => false,
                        'placeholder' => '-- Select --',
                        'query_builder' => $qb,
                    ])
                    ;
            }
        });
    }
}
