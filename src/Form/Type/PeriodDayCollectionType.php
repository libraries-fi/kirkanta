<?php

namespace App\Form\Type;

// use App\Entity\PeriodDay;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PeriodDayCollectionType extends CollectionType
{
    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);

        $options->setDefaults([
            'entry_type' => PeriodDayType::class,
            'entry_options' => [
                // 'label' => false
            ]
        ]);
    }
}
