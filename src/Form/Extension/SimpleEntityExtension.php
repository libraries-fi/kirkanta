<?php

namespace App\Form\Extension;

use Doctrine\ORM\EntityManagerInterface;
use App\Form\ChoiceList\EntityLabelLoader;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SimpleEntityExtension extends AbstractTypeExtension
{
    private $em;

    public function __construct(EntityManagerInterface $entity_manager)
    {
        $this->em = $entity_manager;
    }

    public function getExtendedType() : string
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $options) : void
    {
        $loader_normalizer = function(Options $options, $loader) {
            if ($loader) {
                return $loader;
            }

            $entity_class = $options['class'];
            $label_field = $options['choice_label'];

            /*
             * EntityLabelLoader is simplified and cannot handle cases that include
             * processing a set of pre-loaded choices.
             */
            if (!isset($options['choices']) && $entity_class) {
                return new EntityLabelLoader($this->em, $entity_class, $label_field);
            }
        };

        $options->setDefault('class', null);
        $options->setDefault('langcode', null);
        $options->setNormalizer('choice_loader', $loader_normalizer);
    }
}
