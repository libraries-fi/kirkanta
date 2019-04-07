<?php

namespace App\Form;

use App\Entity\Consortium;
use App\Entity\Region;
use App\Entity\RegionalLibrary;
use App\Util\PersonQualities;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CityForm extends EntityFormType
{
    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        
        $options->setDefaults([
            'data_class' => \App\Entity\City::class,
        ]);
    }

    public function form(FormBuilderInterface $builder, array $options) : void
    {
        parent::form($builder, $options);

        $builder
            ->add('region', EntityType::class, [
                'class' => Region::class,
                'choice_label' => 'name',
                'placeholder' => '- Select -',
            ])
            // ->add('regional_library', EntityType::class, [
            //     'class' => RegionalLibrary::class,
            //     'choice_label' => 'name'
                // 'placeholder' => '- Select -',
            // ])
            ->add('consortium', EntityType::class, [
                'class' => Consortium::class,
                'choice_label' => 'name',
                'placeholder' => '-- Select --',
            ])
            ->add('translations', I18n\EntityDataCollectionType::class, [
                'entry_type' => EntityData\CityDataType::class
            ])
            ;
    }
}
