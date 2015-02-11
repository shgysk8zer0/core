<?php
namespace shgysk8zer0\Core\Traits;

/**
 *
 */
trait PDO
{
	/**
	 * Returns an object where $results->{$name} = $value
	 *
	 * @param string $table Name of table
	 * @return \stdClass
	 */
	final public function nameValue($table)
	{
		$results = new \stdClass();
		foreach ($this("SELECT `name`, `value` FROM `{$table}`") as $row) {
			$results->{$row->name} = $row->value;
		}
		return $results;
	}

	/**
	 * Executes a query and returns the results
	 *
	 * @param  string $query The query to execute
	 * @param  int    $n     Optional result number to return
	 * @return array         Array of stClass objects
	 */
	final public function fetchArray($query, $n = null)
	{
		return (is_int($n)) ? $this($query)[$n] : $this($query);
	}
}
