<?php

namespace App\Form;

use App\Form\Type\StateChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConsortiumForm extends EntityFormType
{
    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);

        $options->setDefaults([
            'data_class' => \App\Entity\Consortium::class,
        ]);
    }

    public function form(FormBuilderInterface $builder, array $options) : void
    {
        parent::form($builder, $options);

        $builder
            ->add('state', StateChoiceType::class)
            // ->add('logo', FileType::class, [
            //     'required' => false,
            //     'data_class' => null
            // ])
            ->add('logo', Type\ConsortiumLogoType::class, [
                'required' => false,
            ])
            ->add('translations', I18n\EntityDataCollectionType::class, [
                'entry_type' => EntityData\ConsortiumDataType::class
            ])
            ;

            $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                $consortium = $event->getData();
                $logo = $consortium->getLogo();

                if ($logo && !$logo->getDefaultLangcode()) {
                    $logo->setDefaultLangcode($consortium->getDefaultLangcode());
                }
            });
    }
}
