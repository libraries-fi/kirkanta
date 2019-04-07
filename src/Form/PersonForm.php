<?php

namespace App\Form;

use App\Entity\Library;
use App\Entity\Person;
use App\Form\DataTransformer\PhoneNumberTransformer;
use App\Form\Type\SimpleEntityType;
use App\Form\Type\StateChoiceType;
use App\Util\PersonQualities;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonForm extends EntityFormType
{
    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        
        $options->setDefaults([
            'data_class' => Person::class,
        ]);
    }

    public function form(FormBuilderInterface $builder, array $options) : void
    {
        parent::form($builder, $options);

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
            ->add('phone', TelType::class, [
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

        $builder->get('phone')->addModelTransformer(new PhoneNumberTransformer);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            if ($event->getData()->isNew()) {
                $event->getData()->setEmailPublic(true);
            }
        });

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use($options) {
            if ($options['context_entity']) {
                $event->getData()->setLibrary($options['context_entity']);
            } else {
                $person = $event->getData();

                if ($person->isNew()) {
                    $groups = $this->auth->getUser()->getGroup()->getTree();
                } else {
                    $groups = $person->getGroup()->getTree();
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
            }
        });
    }
}
