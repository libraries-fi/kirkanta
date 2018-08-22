<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class PhoneNumberTransformer implements DataTransformerInterface
{
    public function transform($source) : ?string
    {
        return $source;
    }

    /**
     * Format user input.
     */
    public function reverseTransform($value)
    {
        $regions = ['02', '03', '05', '06', '08', '09', '013', '014', '015', '016', '017', '018', '019'];
        $operators = ['040', '041', '042', '043', '044', '045', '046', '049', '050'];
        $extra = ['0100', '0200', '0700', '0800'];
        $input = preg_replace('/[\s\(\)]/', '', $value);
        $output = '';

        if (!ctype_digit($input)) {
            throw new TransformationFailedException(sprintf('Passed value \'%s\' is not a valid phone number.', $value));
        }

        foreach (array_merge($operators, $regions, $extra) as $prefix) {
            if (strpos($input, $prefix) === 0) {
                $output = substr($input, 0, strlen($prefix));
                $input = substr($input, strlen($prefix));
                break;
            }
        }

        $mid = floor(strlen($input) / 2);
        $output = sprintf('%s %s %s', $output, substr($input, 0, $mid), substr($input, $mid));
        return trim($output);
    }
}
