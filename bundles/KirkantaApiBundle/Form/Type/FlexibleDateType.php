<?php

namespace KirjastotFi\KirkantaApiBundle\Form\Type;

use DateInterval;
use DateTime;
use InvalidArgumentException;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FlexibleDateType extends DateType
{
    const RANGE_BEGIN = 'begin';
    const RANGE_END = 'end';

    private $position = null;

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            // 'compound' => false,
            'widget' => 'single_text',
            'input' => 'string',
            'range_position' => self::RANGE_BEGIN,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $position = ($options['range_position'] == self::RANGE_END);

        $builder->addViewTransformer(new CallbackTransformer(
            function($data) use($position) {
                return $data;
            },
            function($data) use($position) {
                if (empty($data)) {
                    $data = $position ? '0w' : '0d';
                }

                if (preg_match('/^(\d+)([dwm])$/', $data, $matches)) {
                    list($_, $amount, $unit) = $matches;
                    $interval = new DateInterval('PT1S');

                    switch ($unit) {
                        case 'd':
                            $ref = 'Today';
                            $interval->d = $amount;
                            break;
                        case 'w':
                            // NOTE: Monday as first day is locale-independent since PHP 5.6.23 / 7.0.8.
                            $ref = $position ? 'Sunday this week' : 'Monday this week';
                            $interval->d = $amount * 7;
                            break;
                        case 'm':
                            $ref = $position ? 'Last day of this month' : 'First day of this month';
                            $interval->m = $amount;
                            break;

                        default:
                            throw new InvalidArgumentException(sprintf('Invalid relative date \'%s\'', $data));
                    }

                    $ref1 = (new DateTime('noon'))->add($interval)->format('U');
                    $date = date('Y-m-d', strtotime($ref, $ref1));

                    return $date;
                }

                return $data;
            }
        ));
    }
}
