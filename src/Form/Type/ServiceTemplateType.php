<?php

namespace App\Form\Type;

use App\Entity\Service;
use App\Util\ServiceTypes;
use Symfony\Component\Form\Extension\Core\Type\BaseType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceTemplateType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('name', null, [
                'label' => 'Standard name'
            ])
            ->add('type', ChoiceType::class, [
                'placeholder' => '-- Any --',
                'choices' => new ServiceTypes,
            ])
            ;
    }

    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        $options->setDefaults([
            'data_class' => Service::class
        ]);
    }
}
