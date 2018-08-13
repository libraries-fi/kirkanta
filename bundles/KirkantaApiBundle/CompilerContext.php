<?php

namespace KirjastotFi\KirkantaApiBundle;

use Symfony\Component\HttpFoundation\ParameterBag;

class CompilerContext
{
    private $entity_class;
    public $langcode;
    public $params;
    public $sort;
    public $refs;
    public $with;

    public function __construct(string $entity_class, string $langcode = null, array $params, array $sort = [], array $refs = [], array $with = [])
    {
        $this->entity_class = $entity_class;
        $this->langcode = $langcode;
        $this->params = new ParameterBag($params);
        $this->sort = new ParameterBag($sort);
        $this->refs = new ParameterBag(array_flip($refs));
        $this->with = new ParameterBag(array_flip($with));
    }

    public function getEntityClass() : string
    {
        return $this->entity_class;
    }
}
