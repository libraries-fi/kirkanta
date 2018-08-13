<?php

namespace KirjastotFi\KirkantaApiBundle;

use Doctrine\ORM\QueryBuilder as DoctrineQueryBuilder;

class QueryBuilder extends DoctrineQueryBuilder
{
    private $aliases;

    /**
     * Highly customized for the convenience of the API.
     *
     * When trying to define a join using an alias that already is found in the chain,
     * the join will silently be discarded.
     */
    public function add($dqlPartName, $dqlPart, $append = false)
    {
        if ($dqlPartName == 'join') {
            $join = reset($dqlPart);
            if (isset($this->aliases[$join->getAlias()])) {
                return $this;
            } else {
                $this->aliases[$join->getAlias()] = $join;
            }
        }

        return parent::add($dqlPartName, $dqlPart, $append);
    }
}
