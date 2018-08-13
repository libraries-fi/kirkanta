<?php

namespace App\Module\Translation\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class TranslationItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder->add('source', null, [
            'disabled' => true,
            'required' => false,
            'label' => false,
        ]);
        $builder->add('message', null, [
            'label' => false,
            'required' => false,
        ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            if (!$event->getData()) {
                return;
            }

            $source = $event->getData()['source'];

            if (strpos($source, "\n") !== false) {
                $form = $event->getForm();
                $row_count = substr_count($source, "\n");

                $form->remove('source');
                $form->remove('message');
                $form->add('source', TextareaType::class, [
                    'disabled' => true,
                    'required' => false,
                    'label' => false,
                    'attr' => [
                        'rows' => min($row_count + 1, 4),
                    ]
                ]);
                $form->add('message', TextareaType::class, [
                    'label' => false,
                    'required' => false,
                    'attr' => [
                        'rows' => min($row_count + 1, 4)
                    ]
                ]);
            }
        });
    }
}
