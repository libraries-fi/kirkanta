<?php

namespace App\Form\EntityData;

use App\Entity\ServiceInstanceData;
use App\Form\I18n\EntityDataType;
use App\Form\Type\RichtextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceInstanceDataType extends EntityDataType
{
    protected $dataClass = ServiceInstanceData::class;

    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('name', null, [
                'required' => false,
                'label' => 'Override name',
                'langcode' => $options['langcode'],
            ])
            ->add('short_description', null, [
                'required' => false,
                'langcode' => $options['langcode'],
            ])
            ->add('description', RichtextType::class, [
                'required' => false,
                'langcode' => $options['langcode'],
                'attr' => [
                    'rows' => 6,
                    'richtext' => true,
                ]
            ])
            ->add('price', null, [
                'required' => false,
                'langcode' => $options['langcode'],
            ])
            ->add('website', UrlType::class, [
                'required' => false,
                'langcode' => $options['langcode'],
            ])
            ;
    }
}
