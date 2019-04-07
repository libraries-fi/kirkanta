<?php

namespace App\Form;

use App\Entity\LibraryPhoto;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LibraryPhotoForm extends EntityFormType
{
    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        $options->setDefaults([
            'data_class' => LibraryPhoto::class,
        ]);
    }

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
            ])
            ->add('translations', I18n\EntityDataCollectionType::class, [
                'entry_type' => EntityData\OrganisationPhotoDataType::class
            ])
            ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            if ($event->getData()->isNew()) {
                $event->getForm()->remove('filename');
            } else {
                $event->getForm()->remove('file');
                $event->getForm()->get('filename')->setData($event->getData()->getFilename());
            }
        });

        $builder->addEventListener(FormEvents::POST_SET_DATA, function(FormEvent $event) {
            if ($event->getForm()->has('filename')) {
                $event->getForm()->get('filename')->setData($event->getData()->getFilename());
            }
        });
    }
}
