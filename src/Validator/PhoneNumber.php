<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PhoneNumber extends Constraint
{
    public $message = 'String {{ string }} is not a valid phone number. Only digits and spaces are allowed.';
}
