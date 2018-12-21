<?php

namespace App\Module\Finna\Form;

use App\Entity\Consortium;
use App\Form\ConsortiumForm;
use App\Form\EntityFormType;
use App\Form\I18n\EntityDataCollectionType;
use App\Form\Type\StateChoiceType;
use App\Module\Finna\Entity\DefaultServicePointBinding;
use App\Module\Finna\Entity\FinnaAdditions;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FinnaAdditionsForm extends EntityFormType
{
    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        $options->setDefaults([
            'data_class' => FinnaAdditions::class,
        ]);
    }

    public function form(FormBuilderInterface $builder, array $options) : void
    {
        parent::form($builder, $options);

        $builder
            ->add('state', StateChoiceType::class, [
                // 'property_path' => 'consortium.state'
            ])
            ->add('exclusive', CheckboxType::class, [
                'required' => false,
                'label' => 'Exclusive Finna organisation',
                'help' => 'Check if this organisation is not a library consortium.'
            ])
            ->add('finna_id', null, [
                'required' => true,
                'label' => 'Finna ID',
            ])
            ->add('service_point', ChoiceType::class, [
                // 'class' => Library::class,
                'label' => 'Default service point',
                'choices' => [],
                'choice_label' => 'name',
                'required' => false,
                'translation_domain' => false,
            ])
            ->add('finna_coverage', IntegerType::class, [
                'required' => false
            ])
            ->add('translations', EntityDataCollectionType::class, [
                'entry_type' => FinnaAdditionsDataType::class
            ])
            ->add('consortium', ConsortiumForm::class, [
                'current_langcode' => $options['current_langcode'],
                'data_class' => Consortium::class
            ])
            ;

        $builder->get('consortium')->remove('state');

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            if ($data instanceof FinnaAdditions) {
                $service_points = $this->types->getRepository('service_point')->findBy([
                    'consortium' => $data
                ]);

                $libraries = $this->types->getRepository('library')->findBy([
                    'consortium' => $data
                ]);

                $choices = [];

                foreach (array_merge($libraries, $service_points) as $entity) {
                    $choices[$entity->getId()] = $entity;
                }

                if ($chosen = $data->getServicePoint()) {
                    $choices[$chosen->getId()] = $chosen;
                }

                $form->add('service_point', ChoiceType::class, [
                    'label' => 'Default service point',
                    'placeholder' => '-- Select --',
                    'choices' => $choices,
                    'choice_label' => 'name',
                    'required' => false,
                    'translation_domain' => false,
                ]);
            }
        });
    }
}
