<?php

namespace App\Form\EntityData;

use App\Entity\CityData;
use App\Form\I18n\EntityDataType;
use App\Form\Type\SlugType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CityDataType extends EntityDataType
{
    protected $dataClass = CityData::class;

    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('name', null, [
                'label' => 'Name',
                'langcode' => $options['langcode'],
            ])
            ->add('slug', SlugType::class, [
                'label' => 'Slug',
                'langcode' => $options['langcode'],
                'entity_type' => 'city',
            ])
            ;
    }
}
