<?php

namespace App\Form\I18n;

use App\Form\EntityData\EntityDataTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntityDataType extends AbstractType
{
    /*
     * NOTE: Configuration option 'data_class' is not sufficient alone because
     * that will be set to null when creating a new translation; We need this data
     * regardless so it must be stored in member variable.
     */
    protected $dataClass = null;

    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults([
            'langcode' => null,
            'data_class' => $this->dataClass,
            'new_translation' => false,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        /*
         * Data transformer will not be triggered if we set it in the PRE_SUBMIT event handler.
         * But we also cannot access the owning entity until that event, so we have to pass
         * the owner using a function call.
         */

        if ($options['new_translation']) {
            $transformer = new EntityDataTransformer($this->dataClass, $options['langcode']);
            $builder->addModelTransformer($transformer);

            $builder->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) use($transformer) {
                if ($collection = $event->getForm()->getParent()->getData()) {
                    $parent = $collection->getOwner();
                    $transformer->setOwnerEntity($parent);
                }
            });
        }
    }
}
