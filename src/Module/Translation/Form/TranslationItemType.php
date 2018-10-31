<?php

namespace App\Module\Translation\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class TranslationItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            $message = $this->prepareForForm($event->getData());
            $event->setData($message);
            $form = $event->getForm();

            if (is_array($message['translation'])) {
                $form->add('translation', FormType::class, [
                    'label' => false
                ]);

                foreach (array_values($message['parts']) as $i => $label) {
                    $form->get('translation')->add($i, TextType::class, [
                        'label' => $label,
                        'required' => false,
                        'translation_domain' => false,
                    ]);
                }
            } else {
                $form->add('translation', TextType::class, [
                    'label' => $message['id'],
                    'required' => false,
                    'translation_domain' => false,
                ]);
            }
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event) {
            // exit('POST SUBMIT');
        });

        $builder->addModelTransformer(new CallbackTransformer(
            function(array $message) {
              /**
               * Transformation cannot be done here so it's implemented in
               * an event listener.
               */

               return $message;
            },
            function(array $message) {
                /**
                 * Convert submitted translation chunks back to single-string values.
                 */
                if (is_array($message['translation'])) {
                    $translation = [];
                    foreach ($message['translation'] as $i => $chunk) {
                        $key = $message['keys'][$i];
                        $translation[] = "{$key} {$chunk}";
                    }
                    $message['translation'] = implode('|', $translation);
                }

                return $message;
            }
        ));
    }

    public function finishView(FormView $view, FormInterface $form, array $options) : void
    {
        if ($form->get('translation')->count() > 0) {
            $keys = $form->getData()['keys'];

            foreach ($view->children['translation']->children as $i => $child) {
                $key = $keys[$i];
                $key = str_replace(['{', '}'], '', $key);
                $key = preg_replace('/,(\s*)Inf\[/', '+', $key);
                $key = str_replace(['[', '-'], ['', '-'], $key);
                $child->vars['translation_plural'] = $key;
            }
        }
    }

    private function prepareForForm(array $message) : array
    {
        if (strpos($message['id'], '|')) {
            $sources = preg_split('/(\s*)\|(\s*)/', $message['id']);
            $translations = preg_split('/(\s*)\|(\s*)/', $message['translation']);
            $values = [];
            $message['keys'] = [];

            foreach ($sources as $i => $source) {
                list($key, $label) = preg_split('/(?<=[\}\[\]]) /', $source, 2);
                $values[$i] = null;
                $message['parts'][$i] = $label;
                $message['keys'][$i] = $key;
            }

            foreach ($translations as $i => $translation) {
                list($key, $value) = explode(' ', $translation . ' ', 2);
                $values[$i] = trim($value);
            }

            $message['translation'] = $values;
        }

        return $message;
    }
}
