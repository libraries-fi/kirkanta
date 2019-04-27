<?php

namespace App\Form\Type;

use App\Entity\Feature\StateAwareness;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StateChoiceType extends ChoiceType
{
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        parent::buildForm($builder, $options);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            if ($event->getData() === null) {
                $event->setData(StateAwareness::DRAFT);
            }
        });
    }

    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);

        $options->setDefaults([
            'choices' => [
                'Draft' => StateAwareness::DRAFT,
                'Published' => StateAwareness::PUBLISHED,
            ]
        ]);
    }
}
