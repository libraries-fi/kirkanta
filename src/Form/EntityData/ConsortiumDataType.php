<?php

namespace App\Form\EntityData;

use App\Entity\ConsortiumData;
use App\Form\I18n\EntityDataType;
use App\Form\Type\RichtextType;
use App\Form\Type\SlugType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ConsortiumDataType extends EntityDataType
{
    protected $dataClass = ConsortiumData::class;

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
                'entity_type' => 'consortium',
            ])
            ->add('homepage', null, [
                'label' => 'Homepage',
                'langcode' => $options['langcode'],
            ])
            ->add('description', RichtextType::class, [
                'required' => false,
                'langcode' => $options['langcode'],
            ])
            ;
    }
}
