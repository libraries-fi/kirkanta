<?php

namespace App\Form;

use App\Entity\Feature\GroupOwnership;
use App\Entity\UserGroup;
use App\EntityTypeManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

abstract class EntityFormType extends FormType
{
    protected $types;

    public function __construct(RequestStack $request_stack, Security $auth, EntityTypeManager $types)
    {
        parent::__construct($request_stack, $auth);
        $this->types = $types;
    }

    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        $options->setDefaults([
            'context_entity' => null,
            'disable_ownership' => false,
        ]);
    }

    public function form(FormBuilderInterface $builder, array $options) : void
    {
        if (!$options['disable_ownership']) {
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                $entity = $event->getData();
                $form = $event->getForm();

                if (!$form->getParent() && $entity instanceof GroupOwnership) {
                    $help = 'Changing this value will change user permissions for this record.';

                    if ($this->auth->isGranted('foobar')) {
                        $preferred_groups = $entity->getOwner()->getTree();
                        $form->add('owner', EntityType::class, [
                            'help' => $help,
                            'class' => UserGroup::class,
                            'query_builder' => function($repo) {
                                return $repo->createQueryBuilder('e');
                            },
                            'preferred_choices' => $preferred_groups
                        ]);
                    } else {
                        $group = $this->auth->getUser()->getGroup();
                        do {
                            $choices[] = $group;
                        } while ($group = $group->getParent());

                        $form->add('owner', EntityType::class, [
                            'help' => $help,
                            'class' => UserGroup::class,
                            'choices' => $choices,
                            'attr' => [
                                'data-no-sort' => true
                            ]
                        ]);
                    }
                }
            });
        }
    }
}
