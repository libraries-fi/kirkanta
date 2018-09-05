<?php

namespace App\Form\EntityData;

use App\Entity\LibraryData;
use App\Form\I18n\EntityDataType;
use App\Form\Type\RichtextType;
use App\Form\Type\SlugType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
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
                'langcode' => $options['langcode'],
            ])
            ->add('short_name', null, [
                'required' => false,
                'label' => 'Short name',
                'langcode' => $options['langcode']
            ])
            ->add('slug', SlugType::class, [
                'label' => 'Slug',
                'langcode' => $options['langcode'],
                'entity_type' => 'library',
            ])
            ->add('slogan', null, [
                'required' => true,
                'label' => 'Slogan',
                'langcode' => $options['langcode']
            ])
            ->add('description', RichtextType::class, [
                'required' => false,
                'label' => 'Description',
                'langcode' => $options['langcode']
            ])
            ->add('transit_directions', TextareaType::class, [
                'required' => false,
                'label' => 'Transit directions',
                'langcode' => $options['langcode'],
                'attr' => [
                    'rows' => 4
                ]
            ])
            ->add('parking_instructions', TextareaType::class, [
                'required' => false,
                'label' => 'Parking instructions',
                'langcode' => $options['langcode'],
                'attr' => [
                    'rows' => 4
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'langcode' => $options['langcode']
            ])
            ->add('homepage', UrlType::class, [
                'required' => false,
                'label' => 'Homepage',
                'langcode' => $options['langcode']
            ])
            ->add('building_name', null, [
                'required' => false,
                'label' => 'Building name',
                'langcode' => $options['langcode']
            ])
            ;
    }
}
