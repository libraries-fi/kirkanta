<?php

namespace KirjastotFi\KirkantaApiBundle\Form;

use KirjastotFi\KirkantaApiBundle\Form\Type\NestedDataType;
use KirjastotFi\KirkantaApiBundle\Form\Type\MetaDateRange;
use KirjastotFi\KirkantaApiBundle\Form\Type\OrderByType;
use KirjastotFi\KirkantaApiBundle\Form\Type\TagChoiceType;
use KirjastotFi\KirkantaApiBundle\Form\Type\TagCollectionType;
use App\Util\ServiceTypes;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

class ServiceForm extends ApiFormType
{
    public function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('sort', OrderByType::class, [
                'empty_data' => ['name'],
                'order_by' => [
                    'Distance' => 'distance',
                    'Name' => 'name',
                    'City' => 'city',
                    'Consortium' => 'consortium',
                ]
            ])
            ->add('with', TagChoiceType::class, [
                'choices' => [
                    'Finna data' => 'finna',
                    'Link groups' => 'links',
                ]
            ])
            ->add('created', MetaDateRange::class)
            ->add('id', TagCollectionType::class, [
                'entry_type' => IntegerType::class,
            ])
            ->add('modified', MetaDateRange::class)
            ->add('name')
            ->add('type', TagChoiceType::class, [
                'choices' => new ServiceTypes
            ])
            ->add('slug')
            ->add('consortium', NestedDataType::class)
            ->add('city', NestedDataType::class)


            ;
    }
}
