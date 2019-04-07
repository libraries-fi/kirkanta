<?php

namespace App\Form;

use App\Entity\LibraryData;
use App\Entity\Organisation;
use App\Form\Type\StateChoiceType;
use App\Util\LibraryTypes;
use App\Util\OrganisationTypes;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrganisationForm extends EntityFormType
{
    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        
        $options->setDefaults([
            'data_class' => Organisation::class,
        ]);
    }

    public function form(FormBuilderInterface $builder, array $options) : void
    {
        parent::form($builder, $options);

        $builder
            ->add('state', StateChoiceType::class)
            // ->add('address', AddressType::class, [
            //     // 'required' => false
            // ])
            // ->add('mail_address', MailAddressType::class, [
            //     'required' => false
            // ])
            ->add('translations', I18n\EntityDataCollectionType::class, [
                'entry_type' => EntityData\OrganisationDataType::class,
                'entry_options' => [
                    'data_class' => LibraryData::class
                ]
            ])

            ;
    }
}
