<?php

namespace App\Component\Element;

use Iterator;
use IteratorAggregate;
use App\Component\Iterator\TransformIterator;

class Table implements IteratorAggregate
{
    use RenderableTrait;

    protected $id;
    protected $data;
    protected $columns;
    protected $initial_sorting;

    public static function createFromArray(array $data) : self
    {
        $table = new static($data);

        if ($row = current($data)) {
            $table->setColumns(array_keys($row));
        }

        return $table;
    }

    public function __construct(iterable $data = [])
    {
        $this->data = $data;
        $this->columns = [];
        $this->initial_sorting = [];
    }

    public function setData(iterable $data) : self
    {
        $this->data = $data;
        return $this;
    }

    public function getData() : iterable
    {
        return $this->data;
    }

    public function getId() : ?string
    {
        return $this->id;
    }

    public function setId(string $id) : self
    {
        $this->id = $id;
        return $this;
    }

    public function getIterator() : Iterator
    {
        return new TransformIterator($this->data, function($row, $i) {
            return new Table\Row($this, $row);
        });
    }

    /**
     * Hack that allows sorting to be defined before the table columns are set.
     */
    public function setInitialSorting(array $sorting) : self
    {
        $this->initial_sorting = $sorting;
        return $this;
    }

    public function getColumns() : array
    {
        if ($this->initial_sorting) {
            foreach ($this->columns as $key => $column) {
                $sorting = array_intersect_key($this->initial_sorting, array_flip($column->getMapping()));
                $column->setSorting(reset($sorting));
            }
            $this->initial_sorting = [];
        }
        return $this->columns;
    }

    public function setColumns(array $columns) : self
    {
        $this->columns = [];
        foreach ($columns as $key => $definition) {
            if (is_integer($key) && is_string($definition)) {
                $key = $definition;
                $definition = null;
            }
            $this->addColumn((string)$key, $definition);
        }

        return $this;
    }

    public function addColumn(string $key, $definition, callable $transformer = null) : self
    {
        if (!is_array($definition)) {
            $definition = ['label' => $definition];
        }
        if ($transformer) {
            $definition['transformer'] = $transformer;
        }
        $column = new Table\Column($key, $definition['label'] ?? null);

        if (isset($definition['transformer'])) {
            $column->setTransformer($definition['transformer']);
        }

        if (isset($definition['use_as_template'])) {
            $column->setUseAsTemplate($definition['use_as_template']);
        }

        if (isset($definition['sortable'])) {
            $column->setSortable($definition['sortable']);
        }

        if (isset($definition['sorting'])) {
            $column->setSorting($definition['sorting']);
        }

        if (isset($definition['mapping'])) {
            $column->setMapping($definition['mapping']);
        }

        if (isset($definition['expand'])) {
            $column->setExpand($definition['expand']);
        }

        if (isset($definition['size'])) {
            $column->setSize($definition['size']);
        }

        unset($this->columns[$key]);
        $this->columns[$key] = $column;
        return $this;
    }

    public function removeColumn(string $key) : self
    {
        unset($this->columns[$key]);
        return $this;
    }

    public function setLabel(string $column, string $label) : self
    {
        $this->columns[$column]->setLabel($label);
        return $this;
    }

    public function transform(string $column, callable $transformer) : self
    {
        $this->columns[$column]->setTransformer($transformer);
        return $this;
    }

    public function useAsTemplate(string $column, bool $state = true) : self
    {
        $this->columns[$column]->setUseAsTemplate($state);
        return $this;
    }

    /**
     * Tells whether or not the column value should be considered as a template string.
     *
     * @param $column When null, class will check the column that is currently being iterated on.
     */
    public function columnIsTemplate(string $column = null) : bool
    {
        if (is_null($column)) {
            return $this->getRenderContextValue('column')->getUseAsTemplate();
        } else {
            return $this->columns[$column]->getUseAsTemplate();
        }
    }

    public function setSortable(string $column, bool $state = true, array $mapping = null) : self
    {
        if ($mapping) {
            $this->columns[$column]->setMapping($mapping);
        }
        $this->columns[$column]->setSortable($state);
        return $this;
    }

    public function setSorting(string $column, string $direction = 'asc') : self
    {
        foreach ($this->columns as $key => $object) {
            if ($key == $column) {
                $object->setSortable(true);
                $object->setSorting($direction);
            } else {
                $object->setSorting(null);
            }
        }
        return $this;
    }

    public function dragHandle(string $column) : self
    {
        $this->columns[$column]->setSize(10);

        return $this->useAsTemplate($column)->transform($column, function() {
            return '
                <a class="drag-handle" title="{{ \'Drag row\'|trans }}" data-drag-id="{{ row.id }}">
                    <i class="fas fa-arrows-alt"></i>
                </a>
            ';
        });
    }
}
