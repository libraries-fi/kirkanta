<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Consortium;
use App\Form\Type\AddressType;
use App\Form\Type\MailAddressType;
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

class OrganisationForm extends FormType
{
    public function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('state', StateChoiceType::class)
            ->add('type', ChoiceType::class, [
                'placeholder' => '-- Select --',
                'choices' => new OrganisationTypes,
            ])
            ->add('address', AddressType::class, [
                // 'required' => false
            ])
            ->add('mail_address', MailAddressType::class, [
                'required' => false
            ])
            ->add('translations', I18n\EntityDataCollectionType::class, [
                'entry_type' => EntityData\OrganisationDataType::class,
            ])

            ;
    }
}
