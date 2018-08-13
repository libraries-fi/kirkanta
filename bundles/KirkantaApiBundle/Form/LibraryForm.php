<?php

namespace KirjastotFi\KirkantaApiBundle\Form;

use App\Util\OrganisationBranchTypes;
use App\Util\OrganisationTypes;
use KirjastotFi\KirkantaApiBundle\Form\Type\GeoType;
use KirjastotFi\KirkantaApiBundle\Form\Type\MetaDateRange;
use KirjastotFi\KirkantaApiBundle\Form\Type\NestedDataType;
use KirjastotFi\KirkantaApiBundle\Form\Type\OrderByType;
use KirjastotFi\KirkantaApiBundle\Form\Type\TagChoiceType;
use KirjastotFi\KirkantaApiBundle\Form\Type\TagCollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;

class LibraryForm extends ApiFormType
{
    public function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('scope', ChoiceType::class, [
                'choices' => [
                    'Full' => 'full',
                    'Index' => 'index'
                ]
            ])
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
                    'Mail address' => 'mail_address',

                    'Accessibility' => 'accessibility',
                    'Extra' => 'extra',
                    'Link groups' => 'link_groups',
                    'Mail address' => 'mail_address',
                    'Staff' => 'persons',

                    'Departments' => 'departments',
                    'Pictures' => 'pictures',
                    'Phone numbers' => 'phone_numbers',
                    'Schedules' => 'schedules',
                    'Services' => 'services',
                ]
            ])
            ->add('refs', TagChoiceType::class, [
                'choices' => [
                    'City' => 'city',
                    'Consortium' => 'consortium',
                    'Period' => 'period',
                    'Provincial library' => 'provincial_library',
                    'Region' => 'region',
                ]
            ])
            ->add('branch_type', TagChoiceType::class, [
                'choices' => new OrganisationBranchTypes,
            ])
            ->add('city', NestedDataType::class)
            ->add('created', MetaDateRange::class)
            ->add('consortium', NestedDataType::class)
            ->add('geo', GeoType::class)
            ->add('id', TagCollectionType::class, [
                'entry_type' => IntegerType::class,
            ])
            ->add('identificator', TagCollectionType::class)
            ->add('modified', MetaDateRange::class)
            ->add('name')
            ->add('period', NestedDataType::class, [
                'fields' => [
                    // FIXME: Use custom range converter
                    'start' => TextType::class,
                    'end' => TextType::class,
                ]
            ])
            ->add('region', NestedDataType::class)
            ->add('service', NestedDataType::class)
            ->add('short_name')
            // ->add('type', TagChoiceType::class, [
            //     'choices' => new OrganisationTypes,
            // ])


            ;
    }
}
