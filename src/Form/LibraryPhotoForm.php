<?php

namespace App\Form;

use App\Entity\LibraryPhoto;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class LibraryPhotoForm extends EntityFormType
{
    public function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('filename', null, [
                'label' => 'File',
                'required' => false,
                'mapped' => false,
            ])
            ->add('file', FileType::class, [
                'required' => true,
                // 'mapped' => false
                'help' => 'You can also drag and drop a file from the disk onto the field.',
            ])
            ->add('author', null, [
                'required' => false,
            ])
            ->add('year', IntegerType::class, [
                'required' => false,
                'attr' => [
                    'min' => 1700,
                    'max' => 2999,
                ]
            ])
            ->add('default_picture', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
            ])
            ->add('translations', I18n\EntityDataCollectionType::class, [
                'entry_type' => EntityData\OrganisationPhotoDataType::class
            ])

            ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            if ($event->getData() instanceof LibraryPhoto) {
                $event->getForm()->remove('file');
                $event->getForm()->get('filename')->setData($event->getData()->getFilename());
            } else {
                $event->getForm()->remove('filename');
            }
        });

        $builder->addEventListener(FormEvents::POST_SET_DATA, function(FormEvent $event) {
            if ($event->getForm()->has('filename')) {
                $event->getForm()->get('filename')->setData($event->getData()->getFilename());
            }
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event) {
            if ($event->getForm()->get('default_picture')->getData()) {
                $event->getData()->setWeight(0);
            }
        });
    }
}
