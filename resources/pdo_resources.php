<?php
	namespace shgysk8zer0\Core\resources;

	/**
	 * Wrapper for standard PDO class.
	 *
	 * This class is meant only to be extended and
	 * not used directly. It offers only a protected
	 * __construct method and a public escape.
	 *
	 * @author Chris Zuber <shgysk8zer0@gmail.com>
	 * @package shgysk8zer0\Core
	 * @version 0.9.0
	 * @copyright 2014, Chris Zuber
	 * @license http://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
	 * This program is free software; you can redistribute it and/or
	 * modify it under the terms of the GNU General Public License
	 * as published by the Free Software Foundation, either version 3
	 * of the License, or (at your option) any later version.
	 *
	 * This program is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	 * GNU General Public License for more details.
	 *
	 * You should have received a copy of the GNU General Public License
	 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
	*/
	abstract class pdo_resources implements \shgysk8zer0\Core\Traits\Magic_Methods
	{
		public $connected;
		protected $pdo, $data = [];

		/**
		 * @method __construct
		 * @desc
		 * Gets database connection info from connect.ini (using ini::load)
		 * The default ini file to use is connect, but can be passed another
		 * in the $con argument.
		 *
		 * Uses that data to create a new PHP Data Object
		 *
		 * @param string $con (.ini file to use for database credentials)
		 * @return void
		 * @example parent::__construct($con)
		 */

		protected function __construct($con = 'connect.json')
		{
			$this->pdo = (is_string($con))
				? pdo_connect::load($con)
				: new pdo_connect($con);
			$this->connected = (is_object($this->pdo) and $this->pdo->connected);
		}

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
		 * For lack of a PDO escape, use quote, trimming off the quotations
		 *
		 * @param mixed $str
		 * @return mixed
		 */
		public function escape(&$val)
		{
			if (is_string($val)) {
				$val = preg_replace('/^\'|\'$/', null, $this->pdo->quote($val));
			} elseif (is_array($val)) {
				array_walk($val, [$this, 'escape']);
			}

			return $val;
		}

		/**
		 * For lack of a good ol' escape method in PDO.
		 *
		 * @param string $str
		 * @return string
		*/
		public function quote(&$str)
		{
			$str = $this->pdo->quote((string)$str);
			return $str;
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
			$this->escape($keys);
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
			$this->escape($keys);
			return array_map(function($key) {
				return ':' . preg_replace('/\s/', '_', $key);
			}, $keys);
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
			$this->escape($keys);
			return array_map(function($key) {
				return preg_replace('/\s/', '_', $key);
			}, $keys);
		}

		/**
		 * Restore database connection from a ".sql" file
		 * @param  string $fname [SQL file without the extension]
		 * @return bool          [Whether or not the restore query was successful]
		 */
		public function restore($fname = null)
		{
			return $this->pdo->restore($fname);
		}

		/**
		 * Does a mysqldump and outputs to $filename
		 * @param  string $filename [Name of file to output to]
		 * @return bool             [Whether or not dump was successful]
		 */
		public function dump($filename = null)
		{
			return $this->pdo->dump($filename);
		}

		/**
		 * Returns a 0 indexed array of tables in database
		 *
		 * @param void
		 * @return array     [Array containing all tables in database]
		 */
		public function show_tables()
		{
			$results = $this->pdo->query('SHOW TABLES');
			$tables = $results->fetchAll(\PDO::FETCH_COLUMN, 0);
			return $tables;
		}

		/**
		 * Returns a 0 indexed array of tables in database
		 *
		 * @param void
		 * @return array    [Array containing database names]
		 */
		public function show_databases()
		{
			$results = $this->pdo->query('SHOW DATABASES');
			$databases = $results->fetchAll(\PDO::FETCH_COLUMN, 0);
			return $databases;
		}

		/**
		 * Describe $table, including:
		 * Field {name}
		 * Type {varchar|int... & (length)}
		 * Null (boolean)
		 * Default {value}
		 * Extra {auto_increment, etc}
		 *
		 * @param string $table
		 * @return array
		 * @depreciated
		 */
		public function describe($table = null)
		{
			return $this->pdo->query(
				"DESCRIBE `{$this->escape($table)}"
			)->fetchAll(
				\PDO::FETCH_CLASS
			);
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
	}
