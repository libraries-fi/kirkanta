<?php

namespace KirjastotFi\KirkantaApiBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderByType extends TagChoiceType
{
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        if (!empty($options['order_by'])) {
            $options['choices'] = [];
            foreach ($options['order_by'] as $label => $key) {
                $options['choices'][$label . ' ▲'] = $key;
                $options['choices'][$label . ' ▼'] = '-' . $key;
            }
        }

        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        $options->setDefaults([
            'order_by' => [],
        ]);
    }
}
