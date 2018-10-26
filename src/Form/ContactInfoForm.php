<?php

namespace App\Form;

use App\Entity\ContactInfoGroup;
use App\Entity\Department;
use App\Entity\Library;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;

class ContactInfoForm extends EntityFormType
{
    public function form(FormBuilderInterface $builder, array $options) : void
    {
        if ($options['scope'] != self::NESTED_SCOPE) {
            $builder->add('group', EntityType::class, [
                'required' => false,
                'class' => ContactInfoGroup::class,
                'help' => 'Group contact details together',
                'placeholder' => '-- None --',
                'query_builder' => function($repo) use($options) {
                    return $repo->createQueryBuilder('e')
                      ->andWhere('e.library = :library')
                      ->setParameter('library', $options['context_entity']);
                },
            ]);
        }
        if ($options['context_entity'] instanceof Library) {
            $builder->add('department', EntityType::class, [
                'required' => false,
                'class' => Department::class,
                'choices' => $options['context_entity']->getDepartments(),
                'placeholder' => $options['context_entity']->getName(),
                'help' => 'Attach contact info to a department',
            ]);
        }

        $builder->add('translations', I18n\EntityDataCollectionType::class, [
            'entry_type' => EntityData\ContactInfoDataType::class,
        ]);
    }
}
