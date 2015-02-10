<?php
namespace shgysk8zer0\Core\Traits;

trait Legacy_PDO
{
	/**
	* Chained magic getter and setter
	* @param string $name, array $arguments
	* @example "$pdo->[getName|setName]($value)"
	*/
	public function __call($name, array $arguments)
	{
		$name = strtolower((string)$name);
		$act = substr($name, 0, 3);
		$key = str_replace(' ', '-', substr($name, 3));
		switch($act) {
			case 'get':
				if (array_key_exists($key, $this->data)) {
					return $this->data[$key];
				} else{
					return false;
				}
				break;
			case 'set':
				$this->data[$key] = $arguments[0];
				return $this;
				break;
			default:
				throw new \Exception("Unknown method: {$name} in " . __CLASS__ .'->' . __METHOD__);
		}
	}

	/**
	* Show all keys for entries in $this->data array
	*
	* @param void
	* @return array
	*/
	public function keys()
	{
		return array_keys($this->data);
	}

	/**
	* Converts array_keys to something safe for
	* queries. Returns an array of the converted keys
	*
	* @param array $arr
	* @return array
	*/
	public function columns(array $arr)
	{
		$keys = array_keys($arr);
		$keys = $this->escape($keys);
		return join(', ', array_map(function($key) {
			return "`{$key}`";
		}, $keys));
	}

	/**
	* Converts array_keys to something safe for
	* queries. Returns the same array with converted keys
	*
	* @param array $arr
	* @return array
	*/
	public function prepare_keys(array $arr)
	{
		$keys = array_keys($arr);
		$keys = $this->escape($keys);
		return array_map(function($key) {
			return ':' . preg_replace('/\s/', '_', $key);
		}, $keys);
	}

	public function name_value($table)
	{
		return $this->nameValue($table);
	}

	/**
	* Maps passed array_keys into keys suitable for binding,
	* E.G. "some key" becomes "some_key"
	* @param  array  $arr [Full array, though only keys will be used]
	* @return array       [Indexed array created from array_keys]
	*/
	public function bind_keys(array $arr)
	{
		$keys = array_keys($arr);
		$keys = $this->escape($keys);
		return array_map(function($key) {
			return preg_replace('/\s/', '_', $key);
		}, $keys);
	}

	/**
	* Returns a 0 indexed array of tables in database
	*
	* @param void
	* @return array     [Array containing all tables in database]
	*/
	public function show_tables()
	{
		return $this->showTables();
	}

	/**
	* Returns a 0 indexed array of tables in database
	*
	* @param void
	* @return array    [Array containing database names]
	*/
	public function show_databases()
	{
		return $this('SHOW DATABASES');
	}

	/**
	* Converts array keys into MySQL columns
	* [
	* 	'user' => 'me',
	* 	'password' => 'password'
	* ]
	* becomes '`user`, `password`'
	*
	* @param array $array
	* @return string
	*/
	public function columns_from(array $array)
	{
		$keys = array_keys($array);
		$key_walker = function(&$key) {
			$this->escape($key);
			$key = "`{$key}`";
		};
		array_walk($keys, $key_walker);

		return join(', ', $keys);
	}

	/**
	 * Executes a query and returns the results
	 *
	 * @param  string $query The query to execute
	 * @param  int    $n     Optional result number to return
	 * @return array         Array of stClass objects
	 */
	public function fetch_array($query, $n = null)
	{
		return $this->fetchArray($query);
	}
}
