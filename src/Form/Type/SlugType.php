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
            'help' => 'Changing slug will break bookmarks.',
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
        $view->vars['attr'] += [
            'pattern' => '[a-zåöä][a-z0-9åöä\-]{3,50}',
            'title' => 'Allowed characters are lower-case letters and numbers and a dash. Must start with a letter and be at least four characters long.'
        ];

        if (!$form->getData()) {
            $view->vars['attr'] += [
                'data-sluggable' => true,
                'data-slug-source' => $options['slug_source'],
                'data-slug-langcode' => $options['langcode'],
                'data-slug-url' => $this->urlBuilder->generate('entity.slugger', [
                    'entity_type' => $options['entity_type']
                ]),
            ];
        }
    }
}
