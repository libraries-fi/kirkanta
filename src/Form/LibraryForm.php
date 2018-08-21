<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Consortium;
use App\Entity\Organisation;
use App\Form\Type\AddressType;
use App\Form\Type\MailAddressType;
use App\Form\Type\StateChoiceType;
use App\Util\OrganisationBranchTypes;
use App\Util\OrganisationTypes;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

class LibraryForm extends FormType
{
    public function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('state', StateChoiceType::class)
            ->add('branch_type', ChoiceType::class, [
                'placeholder' => '-- Select --',
                'choices' => new OrganisationBranchTypes
            ])
            ->add('consortium', EntityType::class, [
                'required' => false,
                'class' => Consortium::class,
                'placeholder' => '-- Select --',
                'choice_label' => 'name',
            ])
            ->add('parent', EntityType::class, [
                'label' => 'Parent organisation',
                'required' => false,
                'class' => Organisation::class,
                'placeholder' => '-- Select --',
                'choice_label' => 'name',
            ])
            ->add('isil', null, [
                'required' => false
            ])
            ->add('identificator', null, [
                'required' => false
            ])
            ->add('address', AddressType::class, [
                // 'required' => false
            ])
            ->add('mail_address', MailAddressType::class, [
                'required' => false
            ])
            ->add('trains', null, [
                'required' => false,
                'attr' => [
                    'rows' => 4
                ]
            ])
            ->add('buses', null, [
                'required' => false,
                'attr' => [
                    'rows' => 4
                ]
            ])
            ->add('trams', null, [
                'required' => false,
                'attr' => [
                    'rows' => 4
                ]
            ])
            ->add('construction_year', IntegerType::class, [
                'required' => false
            ])
            ->add('interior_designer', null, [
                'required' => false
            ])
            ->add('building_architect', null, [
                'required' => false
            ])
            ->add('translations', I18n\EntityDataCollectionType::class, [
                'entry_type' => EntityData\LibraryDataType::class,
            ])

            ;
    }
}
