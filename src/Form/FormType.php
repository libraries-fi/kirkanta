<?php

namespace App\Form;

use App\Entity\Feature\StateAwareness;
use App\Form\Type\ActionsType;
use Symfony\Component\Form\AbstractType;
use App\Util\SystemLanguages;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class FormType extends AbstractType
{
    abstract protected function form(FormBuilderInterface $builder, array $options) : void;

    private $requestStack;

    public function __construct(RequestStack $request_stack)
    {
        $this->requestStack = $request_stack;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $this->form($builder, $options);
        $this->actions($builder);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            $entity = $event->getData();

            if ($entity instanceof StateAwareness && $entity->isDeleted()) {
                $event->getForm()->remove('state');

                $event->getForm()->add('restore', HiddenType::class, [
                    'mapped' => false,
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);

        $request = $this->requestStack->getCurrentRequest();
        $langcode = $request->query->get('langcode') ?? SystemLanguages::DEFAULT_LANGCODE;

        $options->setDefaults([
            'admin' => false,
            'scope' => 'full',
            'current_langcode' => $langcode,
        ]);
    }

    protected function actions(FormBuilderInterface $builder) : void
    {
        $builder->add('actions', ActionsType::class, [
            'label' => false,
            'mapped' => false
        ]);
    }
}
