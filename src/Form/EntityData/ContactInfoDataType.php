<?php

namespace App\Form\EntityData;

use App\Entity\ContactInfoData;
use App\Form\I18n\EntityDataType;
use App\Form\Type\RichtextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactInfoDataType extends EntityDataType
{
    protected $dataClass = ContactInfoData::class;

    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('name', null, [
                'langcode' => $options['langcode'],
            ])
            ->add('description', RichtextType::class, [
                'required' => false,
                'langcode' => $options['langcode']
            ])
            ;
    }
}
