<?php

namespace App\Form\EntityData;

use App\Entity\LibraryPhotoData;
use App\Form\I18n\EntityDataType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrganisationPhotoDataType extends EntityDataType
{
    protected $dataClass = LibraryPhotoData::class;

    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('name', null, [
                'required' => true,
                'label' => 'Name',
                'langcode' => $options['langcode'],
                'help' => 'Used as a short description to improve accessibility.',
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'Description',
                'langcode' => $options['langcode'],
                'attr' => [
                    'rows' => 4,
                ]
            ])
            ;
    }
}
