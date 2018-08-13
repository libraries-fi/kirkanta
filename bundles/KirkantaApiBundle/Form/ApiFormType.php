<?php

namespace KirjastotFi\KirkantaApiBundle\Form;

use App\Util\SystemLanguages;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

abstract class ApiFormType extends AbstractType
{
    abstract public function form(FormBuilderInterface $form_builder, array $options) : void;

    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $this->form($builder, $options);

        $builder
            // ->add('lang', ChoiceType::class, [
            //     'choices' => new SystemLanguages
            // ])
            // ->add('page', IntegerType::class, [
            //     'empty_data' => '1'
            // ])
            // ->add('limit', IntegerType::class, [
            //     'empty_data' => '50'
            // ])
            ->add('view', ChoiceType::class, [
                'choices' => [
                    'Default' => 'default',
                    'Index' => 'index',
                ],
                'empty_data' => 'default',
            ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit'], 1000);
    }

    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        $options->setDefaults([
            'csrf_protection' => false
        ]);
    }

    public function onPreSubmit(FormEvent $event) : void
    {
        /*
         * Process query variables that are passed as 'foo:bar=x'.
         *
         * Such data is meant to be consumed by NestedDataType, which expects
         * the data to be in array format.
         */

        $values = [];
        $input = $event->getData();
        krsort($input);

        $form = $event->getForm();

        foreach ($input as $key => $value) {
            if (strpos($key, ':') && !$form->has($key)) {
                list($key, $sub) = explode(':', $key);

                if (isset($values[$key]) && !is_array($values[$key])) {
                    $values[$key]['id'] = $values[$key];
                }

                $values[$key][$sub] = $value;
            } elseif (isset($values[$key]) && is_array($values[$key])) {
                $values[$key]['id'] = $value;
            } else {
                $values[$key] = $value;
            }
        }

        $event->setData($values);
    }
}
