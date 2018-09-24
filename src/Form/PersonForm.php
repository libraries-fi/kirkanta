<?php

namespace App\Form;

use App\Entity\Library;
use App\Entity\Person;
use App\Form\Type\SimpleEntityType;
use App\Form\Type\StateChoiceType;
use App\Util\PersonQualities;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class PersonForm extends EntityFormType
{
    public function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('state', StateChoiceType::class)
            ->add('first_name', null, [
                'required' => true
            ])
            ->add('last_name', null, [
                'required' => true
            ])
            ->add('email', EmailType::class, [
                // 'required' => false
            ])
            ->add('email_public', CheckboxType::class, [
                'label' => 'Email address can be published',
                'required' => false
            ])
            ->add('phone', null, [
                'required' => false
            ])
            ->add('head', CheckboxType::class, [
                'required' => false,
                'label' => 'Head of organisation',
            ])
            ->add('qualities', ChoiceType::class, [
                'choices' => new PersonQualities,
                'multiple' => true,
                'required' => false,
                'expanded' => true,
            ])
            ->add('translations', I18n\EntityDataCollectionType::class, [
                'entry_type' => EntityData\PersonDataType::class
            ])
            ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            if (!$event->getData()) {
                $event->setData(['email_public' => true]);
            }
        });

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use($options) {
            $person = $event->getData();

            if ($person instanceof Person) {
                $groups = $person->getGroup()->getTree();
            } else {
                $groups = $this->auth->getUser()->getGroup()->getTree();
            }

            if ($groups) {
                $event->getForm()->add('library', EntityType::class, [
                    'class' => Library::class,
                    'query_builder' => function($repo) use($groups) {
                        return $repo->createQueryBuilder('e')
                        ->join('e.translations', 'd')
                        ->orderBy('d.name')
                        ->andWhere('e.group IN (:groups)')
                        ->setParameter('groups', $groups)
                        ;
                    }
                ]);
            }
        });
    }
}
