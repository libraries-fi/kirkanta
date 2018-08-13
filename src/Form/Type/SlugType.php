<?php

namespace App\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SlugType extends TextType
{
    private $urlBuilder;

    public function __construct(UrlGeneratorInterface $url_builder)
    {
        $this->urlBuilder = $url_builder;
    }

    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);

        $options->setDefaults([
            'slug_source' => 'name',
            'entity_type' => null,
        ]);
    }

    public function getParent() : string
    {
        return TextType::class;
    }

    public function getBlockPrefix() : string
    {
        return 'slug';
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (!$form->getData()) {
            $view->vars['attr'] += [
                'data-sluggable' => true,
                'data-slug-source' => $options['slug_source'],
                'data-slug-langcode' => $options['langcode'],
                'data-slug-url' => $this->urlBuilder->generate('entity.slugger', [
                    'entity_type' => $options['entity_type']
                ])
            ];
        }
    }
}
