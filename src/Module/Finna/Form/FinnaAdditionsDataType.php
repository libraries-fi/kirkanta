<?php

namespace App\Module\Finna\Form;

use App\Form\EntityData\EntityDataTransformer;
use App\Form\I18n\EntityDataType;
use App\Form\Type\RichtextType;
use App\Module\Finna\Entity\FinnaAdditionsData;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FinnaAdditionsDataType extends EntityDataType
{
    protected $dataClass = FinnaAdditionsData::class;

    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('usage_info', RichtextType::class, [
                'required' => false,
                'label' => 'Usage info',
                'langcode' => $options['langcode'],
                'attr' => [
                    'rows' => 8,
                ]
            ])
            ->add('notification', TextareaType::class, [
                'required' => false,
                'label' => 'Notification',
                'langcode' => $options['langcode'],
                'attr' => [
                    'rows' => 4,
                ]
            ]);
    }
}
