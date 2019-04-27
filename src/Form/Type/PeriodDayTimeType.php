<?php

namespace App\Form\Type;

use App\Entity\Address;
use Symfony\Component\Form\Extension\Core\Type\BaseType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PeriodDayTimeType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        // There were issues with returned value with Symfony 3; check now in Symfony 4.
        // $builder
        //     ->add('opens', TimeType::class, [
        //         'input' => 'string'
        //     ])
        //     ->add('closes', TimeType::class, [
        //         'input' => 'string'
        //     ]);

        $builder
            ->add('opens')
            ->add('closes')
            ->add('staff', CheckboxType::class, [
                'required' => false,
            ]);

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $staff = $event->getForm()->get('staff');

            if ($staff->getData() === null) {
                $staff->setData(true);
            }
        });
    }

    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        $options->setDefaults([
            // 'data_class' => 'string'
        ]);
    }
}
