<?php

namespace App\Form\Type;

use App\Entity\ConsortiumLogo;
use Symfony\Component\Form\Extension\Core\Type\BaseType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConsortiumLogoType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('file', FileType::class)
            ;

        $builder->addEventListener(FormEvents::SUBMIT, function(FormEvent $event) {
            if ($event->getData()->getFile()) {
                $event->getData()->setFilename('');
            }
        });
    }

    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        $options->setDefaults([
            'data_class' => ConsortiumLogo::class,
        ]);
    }
}
