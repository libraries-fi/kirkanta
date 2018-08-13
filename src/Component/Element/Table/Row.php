<?php

namespace App\Component\Element\Table;

use BadMethodCall;
use App\Component\Element\Table;
use App\Component\Iterator\TransformIterator;
use Symfony\Component\PropertyAccess\PropertyAccess;

class Row extends TransformIterator
{
    private $table;
    private $data;

    public function __construct(Table $table, $row = null)
    {
        parent::__construct($table->getColumns(), [$this, 'processColumn']);

        $this->table = $table;
        $this->accessor = PropertyAccess::createPropertyAccessor();

        // PropertyAccessor requires different path notation for arrays vs. objects,
        // so it's easier to force the data to be an object.
        $this->data = is_array($row) ? (object)$row : $row;
    }

    public function __call($key, $params)
    {
        if (empty($params)) {
            return $this->get($key);
        } else {
            throw new BadMethodCall($key);
        }
    }

    public function get($key)
    {
        return $this->accessor->getValue($this->data, $key);
    }

    public function setData($row) : void
    {
        $this->data = $row;
    }

    public function processColumn(Column $column)
    {
        $this->table->setRenderContextValue('column', $column);

        if ($transformer = $column->getTransformer()) {
            $value = $transformer($this->data);
        } else {
            $value = $this->get($column->getKey());
        }
        return $value;
    }
}
