<?php
namespace Cookbook\Database;
#[QueryBuilderBaseBase(
    "Builds an SQL query using OOP Builder pattern",
    "To create a prepared statement w/ placeholders, just supply the placeholders instead of values"
)]
abstract class QueryBuilderBase
{
	public string $sql    = '';
	public string $prefix = '';
	public array $where   = [];
	public array $control = [];
    #[QueryBuilderBase\__construct(
        "array \$cols : desired table columns",
        "string \$table : name of the table",
        "string \$quoteColChar : character used to quote columns",
        "string \$quoteValCar : character used to quote values"
    )]
    public function __construct(
        public array $cols,
        public string $table,
        public string $quoteColChar = '`',
        public string $quoteValChar = '\'') 
    {}
    protected abstract function quoteExp(string $a) : string;
    #[QueryBuilderBase\quoteCol("string \$a : column or table name to be quoted")]
    protected function quoteCol(string $a) : string
    {
        return ($a === '') ? '' : $this->quoteColChar . $a . $this->quoteColChar;
    }
    #[QueryBuilderBase\quoteVal("string \$a : column or table name to be quoted")]
    protected function quoteVal(string $a) : string
    {
        return ($a === '') ? '' : $this->quoteValChar . $a . $this->quoteValChar;
    }
    #[QueryBuilderBase\select("array \$cols : columns to return; if empty, returns all cols")]
    public function select() : static
    {
        $this->sql = '';
        $this->where = [];
        $this->control = [];
        $this->prefix = 'SELECT ';
        foreach ($this->cols as $col)
            $this->prefix .= $this->quoteCol($col) . ',';
        // remove trailing comma
        $this->prefix = substr($this->prefix, 0, -1);
        $this->prefix .= ' FROM ' . $this->quoteCol($this->table) . ' ';
		return $this;
    }
    #[QueryBuilderBase\where("string \$a needs to take the form COL OPERATOR VALUE")]
    public function where(string $a = '') : static
    {
        $this->where[0] = 'WHERE ' 
                        . ((empty($a)) ? '' : $this->quoteExp($a))
                        . ' ';
		return $this;
    }
    #[QueryBuilderBase\and("string \$a needs to take the form COL OPERATOR VALUE")]
    public function and(string $a = '') : static
    {
        $this->where[] = $this->exp($a, 'AND');
		return $this;
    }
    #[QueryBuilderBase\or("string \$a needs to take the form COL OPERATOR VALUE")]
    public function or(string $a = '') : static
    {
        $this->where[] = $this->exp($a, 'OR');
		return $this;
    }
    #[QueryBuilderBase\not("string \$a needs to take the form COL OPERATOR VALUE")]
    public function not(string $a = '')
    {
        $this->where[] = $this->exp($a, 'NOT');
		return $this;
    }
    #[QueryBuilderBase\exp("string \$a needs to take the form COL OPERATOR VALUE",
                       " string \$exp is AND, OR, NOT")]
    public function exp(string $a = '', string $exp = 'AND')
    {
        return ' ' . $exp . ' ' . (((empty($a)) ? '' : $this->quoteExp($a))) . ' ';
    }
    #[QueryBuilderBase\like("string \$a : COL", "string \$b : VALUE")]
    public function like(string $a, string $b) : static
    {
        $this->where[] = $this->quoteCol($a) . ' LIKE ' . $this->quoteVal('%' . $b . '%') . ' ';
		return $this;
    }
    #[QueryBuilderBase\in(
        "string \$col : column name", 
        "array \$a items to be included in the IN clause"
    )]    
    public function in(string $col, array $arr) : static
    {
        $vals = '';
        foreach ($arr as $item) {
            $vals .= $this->quoteVal($item) . ',';
        }
        $this->where[] = $this->quoteCol($col) . ' IN ( ' . substr($vals, 0, -1) . ' )';
		return $this;
    }
    #[QueryBuilderBase\limit("int \$num : represents how many rows in the output")]    
    public function limit(int $num) : static
    {
        $this->control[0] = ' LIMIT ' . $num;
		return $this;
    }
    #[QueryBuilderBase\offset("int \$num : represents how many rows to skip")]    
    public function offset(int $num) : static
    {
        $this->control[1] = ' OFFSET ' . $num;
		return $this;
    }
    #[QueryBuilder\getSql("returns the SQL string")]    
	public function getSql() : string
	{
		$this->sql = $this->prefix
				. implode(' ', $this->where)
				. ' '
				. ($this->control[0] ?? '')
				. ' '
				. ($this->control[1] ?? '');
		$this->sql = preg_replace('/\s+/', ' ', $this->sql);
		return trim($this->sql);
	}
}
