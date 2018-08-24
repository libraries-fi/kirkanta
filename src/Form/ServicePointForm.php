<?php

namespace App\Form;

use App\Entity\Consortium;
use App\Entity\ServicePoint;
use App\Util\ServicePointTypes;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class ServicePointForm extends LibraryForm
{
    public function form(FormBuilderInterface $builder, array $options) : void
    {
        parent::form($builder, $options);

        /*
         * Override fields to change options.
         */
        $builder
            ->add('type', ChoiceType::class, [
                'placeholder' => '-- Select --',
                'choices' => new ServicePointTypes
            ])
            ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use($options) {
            $library = $event->getData();

            if ($library instanceof ServicePoint) {
                $groups = $library->getGroup()->getTree();
            } else {
                $groups = $this->auth->getUser()->getGroup()->getTree();
            }

            if ($groups) {
                $event->getForm()->add('consortium', EntityType::class, [
                    'class' => Consortium::class,
                    'label' => 'Finna organisation',
                    'required' => false,
                    'placeholder' => '-- Select --',
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

            /*
             * Replace unused fields with hidden values.
             * This allows us to re-use library form templates.
             */
            $event->getForm()
                ->add('parent', HiddenType::class, [
                    'mapped' => false,
                    'required' => false,
                    'data' => null,
                ]);
        });
    }
}
