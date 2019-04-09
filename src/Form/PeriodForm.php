<?php

namespace App\Form;

use DateTimeImmutable;
use App\Entity\Department;
use App\Entity\Library;
use App\Entity\Period;
use App\Util\FormData;
use App\Util\PeriodSections;
use App\Form\Type\PeriodDayCollectionType;
use App\Form\Type\PeriodDayType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PeriodForm extends EntityFormType
{
    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);

        $options->setDefaults([
            'data_class' => Period::class,
        ]);
    }

    public function form(FormBuilderInterface $builder, array $options) : void
    {
        parent::form($builder, $options);

        $builder
            ->add('valid_from', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
            ])
            ->add('valid_until', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd'
            ])
            ->add('translations', I18n\EntityDataCollectionType::class, [
                'entry_type' => EntityData\PeriodDataType::class
            ])
            ;

        if ($options['context_entity'] instanceof Library) {
            $library = $options['context_entity'];
            $builder->add('department', EntityType::class, [
                'required' => false,
                'class' => Department::class,
                'choices' => $library->getDepartments(),
                'placeholder' => $library->getName(),
                'help' => 'Attach contact info to a department',
            ]);
        }

        // Periods are marked non-legacy when they are saved as the data will be converted.
        $builder->add('is_legacy_format', CheckboxType::class, [
            'data' => false
        ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use($options) {
            $form = $event->getForm();
            $period = $event->getData();

            if (!$period) {
                $organisation = $options['context_entity'];
            } else {
                $this->fixLegacyFormatDayTranslations($period);
                $organisation = $period->getParent();
            }
        });

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use($options) {
            $langcodes = [$event->getForm()->getRoot()->getConfig()->getOptions()['current_langcode']];

            $period = $event->getData();

            if ($period instanceof Period) {
                $langcodes = array_merge($langcodes, $period->getTranslations()->getKeys());
            }

            if (isset($options['context_entity'])) {
                $langcodes[] = $options['context_entity']->getDefaultLangcode();
            }

            $langcodes = array_unique(array_filter($langcodes));

            $event->getForm()->add('days', PeriodDayCollectionType::class, [
                'allow_add' => true,
                'allow_delete' => true,
                'entry_options' => [
                    'available_languages' => $langcodes
                ],
            ]);
        });

        $builder->addEventListener(FormEvents::POST_SET_DATA, function(FormEvent $event) {
            $from = $event->getForm()->get('valid_from');

            if (!$from->getData()) {
                $from->setData(new DateTimeImmutable);
            }
        });

        $builder->addEventListener(FormEvents::POST_SET_DATA, function(FormEvent $event) {
            $days = $event->getForm()->get('days');

            if (!$days->getData()) {
                $days->setData(array_fill(0, 7, []));
            }
        });
    }

    private function fixLegacyFormatDayTranslations(Period $period) : void
    {
        /*
         * In previous Kirkanta 'info' was the Finnish translation for day info; now it must be
         * an array of all translations.
         */

        $days = $period->getDays();
        foreach ($days as &$day) {
            if (is_string($day['info'])) {
                $day['info'] = ['fi' => $day['info']];
            }
        }

        $period->setDays($days);
    }
}
