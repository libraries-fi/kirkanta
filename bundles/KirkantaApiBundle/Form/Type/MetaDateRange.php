<?php

namespace KirjastotFi\KirkantaApiBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;

class MetaDateRange extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        parent::buildForm($builder, $options);

        $builder->add('after', DateType::class, [
            'widget' => 'single_text'
        ]);
        $builder->add('before', DateType::class, [
            'widget' => 'single_text'
        ]);
    }
}
