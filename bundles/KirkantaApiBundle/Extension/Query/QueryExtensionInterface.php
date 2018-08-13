<?php

namespace KirjastotFi\KirkantaApiBundle\Extension\Query;

use Doctrine\ORM\QueryBuilder;
use KirjastotFi\KirkantaApiBundle\CompilerContext;

interface QueryExtensionInterface
{
    public function supports(CompilerContext $context) : bool;
    public function build(QueryBuilder $builder, CompilerContext $context) : void;
}
