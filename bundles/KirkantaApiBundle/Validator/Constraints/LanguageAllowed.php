<?php

namespace KirjastotFi\KirkantaApiBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class LanguageAllowed extends Constraint
{
    public $message = 'Langcode {{ langcode }} is not allowed. Valid values are {{ langcodes }}';
}
