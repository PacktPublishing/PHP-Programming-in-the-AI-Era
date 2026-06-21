<?php
namespace Cookbook\Database;
use InvalidArgumentException;
#[QueryBuilderWithPrep(
    "Builds an SQL query using OOP Builder pattern for use as prepared statement with named placeholders",
    "To create a prepared statement w/ placeholders, just supply the placeholders instead of values"
)]
class QueryBuilderWithPrep extends QueryBuilderBase
{
    public array $values = [];
    #[QueryBuilder\quoteExp("string \$a needs to take the form COL OPERATOR VALUE")]
    protected function quoteExp(string $a) : string
    {
        // get rid of double space
        $a = preg_replace('/  /', ' ', $a);
        // break up into column, operator, value
        $list = explode(' ', $a);
        // check to ensure COL OPERATOR VALUE form is used
        if (empty($list) || count($list) < 3) {
            throw new InvalidArgumentException(static::ERR_EXP);
        }
        $col  = trim(array_shift($list));
        $op   = trim(array_shift($list));
        $this->values[$col] = trim(implode(' ', $list ?? []));
        return $this->quoteCol($col) . ' ' . $op . ' :' . $col;
    }
}
