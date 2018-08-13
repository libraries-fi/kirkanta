<?php

namespace KirjastotFi\KirkantaApiBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;

class GeoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('pos', TextType::class, [
                'constraints' => [
                    new Regex('/^\d+(\.\d+)?,\d+(\.\d+)?$/')
                ]
            ])->add('dist', IntegerType::class, [
                'empty_data' => '10'
            ]);
    }
}
