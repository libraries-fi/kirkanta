<?php

namespace App\Module\Finna\Form;

use App\Entity\Consortium;
use App\Form\ConsortiumForm;
use App\Form\FormType;
use App\Form\I18n\EntityDataCollectionType;
use App\Form\Type\RichtextType;
use App\Form\Type\StateChoiceType;
use App\Module\Finna\Entity\FinnaAdditions;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FinnaAdditionsForm extends FormType
{
    public function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('state', StateChoiceType::class, [
                // 'property_path' => 'consortium.state'
            ])
            ->add('exclusive', CheckboxType::class, [
                'required' => false,
                'label' => 'Exclusive Finna organisation',
                'help' => 'Check if this organisation is not a library consortium.'
            ])
            ->add('finna_id', null, [
                'required' => true,
                'label' => 'Finna ID',
            ])
            // ->add('service_point', EntityType::class, [
            //     'class' => Library::class,
            //     'choice_label' => 'name',
            //     'required' => false,
            // ])
            ->add('finna_coverage', IntegerType::class, [
                'required' => false
            ])
            ->add('usage_info', RichtextType::class, [
                'required' => false,
                'attr' => [
                    'rows' => 8,
                ]
            ])
            ->add('notification', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'rows' => 4,
                ]
            ])
            ->add('translations', EntityDataCollectionType::class, [
                'entry_type' => FinnaAdditionsDataType::class
            ])
            ->add('consortium', ConsortiumForm::class, [
                'current_langcode' => $options['current_langcode'],
                'data_class' => Consortium::class
            ])
            ;

        $builder->get('consortium')->remove('state');
    }
}
