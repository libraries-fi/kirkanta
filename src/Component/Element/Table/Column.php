<?php

namespace App\Component\Element\Table;

class Column
{
    private $key;
    private $label;
    private $transformer;
    private $options;

    public function __construct(string $key, string $label = null)
    {
        $this->key = $key;
        $this->label = $label;
        $this->options = [];
    }

    public function getKey() : string
    {
        return $this->key;
    }

    public function getLabel() : string
    {
        if (is_null($this->label)) {
            $this->label = ucfirst(str_replace('_', ' ', $this->key));
        }
        return $this->label;
    }

    public function setLabel(string $label) : self
    {
        $this->label = $label;
        return $this;
    }

    public function setTransformer(callable $transformer) : self
    {
        $this->transformer = $transformer;
        return $this;
    }

    public function getTransformer() : ?callable
    {
        return $this->transformer;
    }

    public function setUseAsTemplate(bool $state) : self
    {
        $this->options['use_as_template'] = $state;
        return $this;
    }

    public function getUseAsTemplate() : bool
    {
        return !empty($this->options['use_as_template']);
    }

    public function setSortable(bool $state) : self
    {
        $this->options['sortable'] = $state;
        return $this;
    }

    public function isSortable() : bool
    {
        return !empty($this->options['sortable']);
    }

    public function setSorting(?string $direction) : self
    {
        $this->options['sorting'] = $direction;
        return $this;
    }

    public function getSorting() : ?string
    {
        return $this->options['sorting'] ?? null;
    }

    public function setMapping(array $fields) : self
    {
        $this->options['mapping'] = $fields;
        return $this;
    }

    public function getMapping() : array
    {
        return $this->options['mapping'] ?? [$this->getKey()];
    }

    public function setExpand(bool $state) : self
    {
        $this->options['expand'] = $state;
        return $this;
    }

    public function getExpand() : bool
    {
        return !empty($this->options['expand']);
    }
}
