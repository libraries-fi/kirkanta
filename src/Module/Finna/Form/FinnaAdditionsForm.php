<?php

namespace App\Module\Finna\Form;

use App\Entity\Consortium;
use App\Form\ConsortiumForm;
use App\Form\EntityFormType;
use App\Form\I18n\EntityDataCollectionType;
use App\Form\Type\RichtextType;
use App\Form\Type\StateChoiceType;
use App\Module\Finna\Entity\DefaultServicePointBinding;
use App\Module\Finna\Entity\FinnaAdditions;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FinnaAdditionsForm extends EntityFormType
{
    public function form(FormBuilderInterface $builder, array $options) : void
    {
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
            ])
            ->add('finna_coverage', IntegerType::class, [
                'required' => false
            ])
            ->add('usage_info', RichtextType::class, [
                'required' => false,
                'attr' => [
                    'rows' => 8,
                ]
            ])
            ->add('notification', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'rows' => 4,
                ]
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

            $user_group = $data instanceof FinnaAdditions
                ? $data->getOwner()
                : $this->auth->getUser()->getGroup();

            $service_points = $this->types->getRepository('service_point')->findBy([
                'group' => $user_group->getTree()
            ]);

            $libraries = $this->types->getRepository('library')->findBy([
                'group' => $user_group->getTree()
            ]);

            $choices = [];

            foreach (array_merge($libraries, $service_points) as $entity) {
                $choices[$entity->getId()] = new DefaultServicePointBinding($entity);
            }

            if ($chosen = $data->getServicePoint()) {
                $choices[$chosen->getId()] = $chosen;
            }

            $form->add('service_point', ChoiceType::class, [
                'label' => 'Default service point',
                'choices' => $choices,
                'choice_label' => 'name',
                'required' => false,
            ]);
        });
    }
}
