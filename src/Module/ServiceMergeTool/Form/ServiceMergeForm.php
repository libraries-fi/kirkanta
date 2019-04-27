<?php

namespace App\Module\ServiceMergeTool\Form;

use App\Entity\Service;
use App\Form\FormType;
use App\Form\Type\StateChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class ServiceMergeForm extends FormType
{
    public function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            // ->add('keep', ChoiceType::class, [
            //     'label' => 'Keep this instance',
            // ])
            ->add('services', CollectionType::class, [
                'entry_type' => Type\ServiceType::class,
                'entry_options' => [
                    // 'current_langcode' => $options['current_langcode'],
                ]
            ])
            ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();
            $services = $event->getData()->getServices();
            $choices = array_map(function ($s) {
                return $s->getId();
            }, $services);

            $form->add('keep', EntityType::class, [
                'label' => 'Keep this instance',
                'class' => Service::class,
                'choices' => new \Doctrine\Common\Collections\ArrayCollection($services),
            ]);

            if (!$data->keep) {
                $data->keep = reset($services);
            }

            $enabled_languages = [];

            foreach ($services as $service) {
                $enabled_languages = array_merge($enabled_languages, $service->getTranslations()->getKeys());
            }

            $enabled_languages = array_unique($enabled_languages);

            $form->add('content_language', \App\Form\I18n\ContentLanguageChoiceType::class, [
                'enabled_languages' => $enabled_languages,
            ]);

            $data->content_language = reset($enabled_languages);
        });
    }
}
