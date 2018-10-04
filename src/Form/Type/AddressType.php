<?php

namespace App\Form\Type;

use App\Entity\Address;
use App\Entity\City;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BaseType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('street')
            ->add('area', null, [
                'required' => false,
            ])
            ->add('info', null, [
                'required' => false,
            ])
            ->add('zipcode')
            ->add('city', EntityType::class, [
                'placeholder' => '-- Select --',
                'class' => City::class,
                'choice_label' => 'name',
            ])
            ->add('coordinates', null, [
                'required' => false
            ])
            ;
    }

    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        $options->setDefaults([
            'data_class' => Address::class,
            'langcode' => null,
        ]);
    }
}
