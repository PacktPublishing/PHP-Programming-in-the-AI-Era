<?php
namespace Cookbook\Database;
use InvalidArgumentException;
#[QueryBuilder(
    "Builds an SQL query using OOP Builder pattern",
    "To create a prepared statement w/ placeholders, just supply the placeholders instead of values"
)]
class QueryBuilder extends QueryBuilderBase
{
    public const ERR_EXP = 'Expressions need to take this form: COL OPERATOR VALUE';
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
        // glue together any remaining values into one
        $val  = trim(implode(' ', $list ?? []));
        return $this->quoteCol($col) . ' ' . $op . ' ' . $this->quoteVal($val);
    }
}
