<?php

namespace App\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResourceImportForm extends FormType
{

    public function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder->add('templates', EntityType::class, [
            'expanded' => true,
            'multiple' => true,
            'required' => true,
            'choice_label' => 'standard_name',
            'class' => $options['entity_type'],
            'group_by' => 'type',

            'query_builder' => function(EntityRepository $repo) use($options) {
                $qb = $repo->createQueryBuilder('e')
                    ->andWhere('e.group IN (:groups)')
                    ->andWhere('e.parent IS NULL')
                    ->setParameter('groups', $options['user_groups']);

                return $qb;
                exit('build query');
            }
        ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            // var_dump($event->getData());
        });

    }

    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        $options->setDefaults([
            'user_groups' => [],
            'entity_type' => null,
        ]);
    }
}
