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
		trigger_error(__METHOD__ . ' is deprecated', E_USER_DEPRECATED);
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
	final public function keys()
	{
		trigger_error(__METHOD__ . ' is deprecated', E_USER_DEPRECATED);
		return array_keys($this->data);
	}

	/**
	* Converts array_keys to something safe for
	* queries. Returns an array of the converted keys
	*
	* @param array $arr
	* @return array
	*/
	final public function columns(array $arr)
	{
		trigger_error(__METHOD__ . ' is deprecated', E_USER_DEPRECATED);
		return join(', ', array_map(function($key) {
			return "`{$key}`";
		}, array_map([$this, 'escape'], array_keys($arr))));
	}

	/**
	* Converts array_keys to something safe for
	* queries. Returns the same array with converted keys
	*
	* @param array $arr
	* @return array
	*/
	final public function prepare_keys(array $arr = array())
	{
		trigger_error(__METHOD__ . ' is deprecated', E_USER_DEPRECATED);
		return array_map(function($key) {
			return ':' . preg_replace('/\s/', '_', $key);
		}, array_map([$this, 'escape'], array_keys($arr)));
	}

	final public function name_value($table)
	{
		trigger_error(__METHOD__ . ' is deprecated', E_USER_DEPRECATED);
		return $this->nameValue($table);
	}

	/**
	* Maps passed array_keys into keys suitable for binding,
	* E.G. "some key" becomes "some_key"
	* @param  array  $arr [Full array, though only keys will be used]
	* @return array       [Indexed array created from array_keys]
	*/
	final public function bind_keys(array $arr = array())
	{
		trigger_error(__METHOD__ . ' is deprecated', E_USER_DEPRECATED);
		return array_map(function($key) {
			return preg_replace('/\s/', '_', $key);
		}, array_map([$this, 'escape'], array_keys($arr)));
	}

	/**
	* Returns a 0 indexed array of tables in database
	*
	* @param void
	* @return array     [Array containing all tables in database]
	*/
	final public function show_tables()
	{
		trigger_error(__METHOD__ . ' is deprecated', E_USER_DEPRECATED);
		return $this->showTables();
	}

	/**
	* Returns a 0 indexed array of tables in database
	*
	* @param void
	* @return array    [Array containing database names]
	*/
	final public function show_databases()
	{
		trigger_error(__METHOD__ . ' is deprecated', E_USER_DEPRECATED);
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
	final public function columns_from(array $array = array())
	{
		trigger_error(__METHOD__ . ' is deprecated', E_USER_DEPRECATED);
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
	final public function fetch_array($query, $n = null)
	{
		trigger_error(__METHOD__ . ' is deprecated', E_USER_DEPRECATED);
		return $this->fetchArray($query);
	}
}
