<?php

namespace KirjastotFi\KirkantaApiBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class LanguageAllowedValidator extends ConstraintValidator
{
    private $langcodes;

    public function __construct(array $langcodes)
    {
        $this->langcodes = $langcodes;
    }

    public function validate($value, Constraint $constraint) : void
    {
        if (!is_null($value) && !in_array($value, $this->langcodes, true)) {
            $this->context->buildViolation($constraint->message)
            ->setParameter('{{ langcode }}', $value)
            ->setParameter('{{ langcodes }}', implode(', ', $this->langcodes))
            ->addViolation();
        }
    }
}
