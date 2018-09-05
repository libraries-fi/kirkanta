<?php

namespace App\Form;

use App\EntityTypeManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

abstract class EntityFormType extends FormType
{
    protected $types;

    public function __construct(RequestStack $request_stack, Security $auth, EntityTypeManager $types)
    {
        parent::__construct($request_stack, $auth);
        $this->types = $types;
    }

    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        $options->setDefaults([
            'context_entity' => null,
        ]);
    }
}
