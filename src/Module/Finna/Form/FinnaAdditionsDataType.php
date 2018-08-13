<?php

namespace App\Module\Finna\Form;

use App\Module\Finna\Entity\FinnaAdditionsData;
use App\Form\I18n\EntityDataType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\EntityData\EntityDataTransformer;

class FinnaAdditionsDataType extends EntityDataType
{
    protected $dataClass = FinnaAdditionsData::class;

    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('usage_info', null, [
                'label' => 'Usage info',
                'langcode' => $options['langcode'],
            ])
            ->add('notification', null, [
                'label' => 'Notification',
                'langcode' => $options['langcode'],
            ]);
    }
}
