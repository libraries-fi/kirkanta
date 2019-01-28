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
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServicePointForm extends LibraryForm
{
    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        $options->setDefaults([
            'data_class' => ServicePoint::class,
        ]);
    }

    public function form(FormBuilderInterface $builder, array $options) : void
    {
        parent::form($builder, $options);

        /*
         * Override fields to change options.
         */
        $builder
            ->remove('main_library')
            ->add('type', ChoiceType::class, [
                'placeholder' => '-- Select --',
                'choices' => new ServicePointTypes
            ])
            ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use($options) {
            $library = $event->getData();

            if ($library->isNew()) {
                $groups = $this->auth->getUser()->getGroup()->getTree();
            } else {
                $groups = $library->getGroup()->getTree();
            }

            if ($groups) {
                $event->getForm()->add('consortium', EntityType::class, [
                    'class' => Consortium::class,
                    'label' => 'Finna organisation',
                    'required' => false,
                    'placeholder' => '-- Select --',
                    'query_builder' => function($repo) use($groups) {
                        return $repo->createQueryBuilder('e')
                        ->join('e.translations', 'd', 'WITH', 'd.langcode = :langcode')
                        ->orderBy('d.name')
                        ->andWhere('e.group IN (:groups)')
                        ->setParameter('groups', $groups)
                        ->setParameter('langcode', 'fi')
                        ;
                    }
                ]);
            }

            /*
             * Replace unused fields with hidden values.
             * This allows us to re-use library form templates.
             */
            $event->getForm()
                ->add('organisation', HiddenType::class, [
                    'mapped' => false,
                    'required' => false,
                    'data' => null,
                ]);
        });
    }
}
