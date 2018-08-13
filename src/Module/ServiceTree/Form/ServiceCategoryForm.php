<?php

namespace App\Module\ServiceTree\Form;

use App\Entity\Service;
use App\Form\FormType;
use App\Form\Type\StateChoiceType;
use App\Module\ServiceTree\Entity\ServiceCategory;
use App\Module\ServiceTree\Form\Type\ServiceItemType;
use App\Module\ServiceTree\Form\Type\ServiceTreeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class ServiceCategoryForm extends FormType
{
    public function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('name')
            ->add('parent', EntityType::class, [
                'required' => false,
                'placeholder' => '-- Select --',
                'class' => ServiceCategory::class,
                'choice_label' => 'name',
            ])
            ->add('sticky', CheckboxType::class, [
                'label' => 'Pinned to top',
                'required' => false,
            ])
            ->add('state', StateChoiceType::class)
            ;

        $builder->addEventListener(FormEvents::POST_SET_DATA, function(FormEvent $event) {
            $form = $event->getForm();

            $form->add('services', EntityType::class, [
                'label' => false,
                'placeholder' => '-- Select --',
                'mapped' => false,
                'class' => Service::class,
                'required' => false,
            ]);

            $form->add('add_service', SubmitType::class, [
                'label' => 'Add',
            ]);

            if ($entity = $event->getData()) {
                $form->add('items', CollectionType::class, [
                    'entry_type' => ServiceItemType::class
                ]);

                // $form->add('root', ServiceTreeType::class, [
                //     'data' => $entity->getRoot()
                //     // 'mapped' => false
                // ]);
            }
        });
    }
}
