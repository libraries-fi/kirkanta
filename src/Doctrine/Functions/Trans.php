<?php

namespace App\Doctrine\Functions;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Literal;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Returns translated value for given property. Will work with PostgreSQL only.
 *
 * Syntax 1: TRANS(my_lang_parameter, e.property)
 * Syntax 2: TRANS('en', e.property)
 */
class Trans extends FunctionNode
{
    /**
     * Langcode for the standalone column.
     */
    private $primary_langcode = 'fi';

    private $property;
    private $langcode;

    public function parse(Parser $parser) : void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->langcode = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->property = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $walker) : string
    {
        $property = $this->property->dispatch($walker);
        $langcode = $this->langcode->dispatch($walker);

        if ($this->langcode instanceof Literal) {
            // Strip quotes.
            $langcode_value = substr($langcode, 1, -1);
        } else {
            $langcode_value = $walker->getQuery()->getParameter($this->langcode->name)->getValue();
        }

        if ($langcode_value == $this->primary_langcode) {
            /*
             * This hack ensures that the same (number of) params are used that is also present
             * in the DQL statement. Could not hack around the query validation checks otherwise.
             */
            return "SUBSTRING({$langcode} || {$property} FROM 3)";
        } else {
            list($prefix, $column) = explode('.', $property);
            return "{$prefix}.translations->{$langcode}->>'{$column}'";
        }
    }
}
