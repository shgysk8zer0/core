<?php

	namespace shgysk8zer0\Core;
	/**
	 * Wrapper for standard PDO class. Allows
	 * standard MySQL to be used, while giving benefits
	 * of chained prepare->bind->execute...
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
	 *
	 * @todo Remove Prepared methods and move to another class.
	 * @todo Move methods into traits.
	 * @todo Remove bloat methods.
	*/
	use \shgysk8zer0\Core\Resources\PDO_Resources as PDO_Resources;
	class PDO extends PDO_Resources
	{
		protected $pdo, $prepared, $connect;
		private $query;
		protected static $instances = [];

		/**
		 * Static load function avoids creating multiple instances/connections
		 * It stores an array of instances in the static instances array.
		 * It uses $con as the key to the array, and the PDO instance as
		 * the value.
		 *
		 * @method load
		 * @param  string $con [.ini file to use for database credentials]
		 * @return self
		 * @example $pdo = PDO::load or $pdo = PDO::load('connect')
		 */
		public static function load($con = 'connect')
		{
			if (!array_key_exists($con, self::$instances)) {
				self::$instances[$con] = new self($con);
			}
			return self::$instances[$con];
		}

		/**
		 * Gets database connection info from config file
		 * The default config file to use is connect, but can be passed another
		 * in the $con argument.
		 *
		 * Uses that data to create a new PHP Data Object
		 *
		 * @method __construct
		 * @param  string      $con [.ini file to use for database credentials]
		 * @example $pdo = new \shgysk8zer0\Core\PDO()
		 */
		public function __construct($con = 'connect')
		{
			parent::__construct($con);
		}

		/**
		 * Argument $query is a SQL query in prepared statement format
		 * "SELECT FROM `$table` WHERE `column` = ':$values'"
		 * Note the use of the colon. These are what we are going to be
		 * binding values to a little later
		 *
		 * Returns $this for chaining. Most further functions will do the same where useful
		 * @method prepare
		 * @param  string $query  [Any given MySQL query]
		 * @return self
		 */

		public function prepare($query)
		{
			$this->prepared = $this->pdo->prepare($query);
			return $this;
		}

		/**
		 * Binds values to prepared statements
		 *
		 * @param array $array    [:key => value]
		 * @return self
		 * @example $pdo->prepare(...)->bind([
		 * 	'col_name' => $value,
		 * 	'col2' => 'something else'
		 * ])
		 */
		public function bind(array $array)
		{
			foreach($array as $paramater => $value) {
				$this->prepared->bindValue(':' . $paramater, $value);
			}
			return $this;
		}

		/**
		 * Executes prepared statements. Does not return results
		 *
		 * @param void
		 * @return self
		 */

		public function execute()
		{
			if ($this->prepared->execute()) {
				return $this;
			}
			return false;
		}

		/**
		 * Gets results of prepared statement. $n can be passed to retreive a specific row
		 *
		 * @param int $n   [Optional index for single result to return]
		 * @return mixed
		 */
		public function get_results($n = null)
		{
			$results = $this->prepared->fetchAll(\PDO::FETCH_CLASS);
			//If $n is set, return $results[$n] (row $n of results) Else return all
			if (empty($results)) {
				return false;
			}

			return (is_int($n)) ? $results[$n] : $results;
		}

		/**
		 * Need PDO method to close database connection
		 *
		 * @param void
		 * @return void
		 * @todo Make it actually close the connection
		 * @todo Extend to __destruct
		 */
		public function close()
		{
			unset($this->pdo);
			unset($this);
		}

		/**
		 * Get the results of a SQL query
		 *
		 * @param string $query
		 * @return mixed
		 */
		public function query($query)
		{
			return $this->pdo->query((string)$query);
		}

		/**
		 * Return the results of a query as an associative array
		 *
		 * @param string $query
		 * @param int $n
		 * @return array
		 */
		public function fetch_array($query = null, $n = null)
		{
			$data = $this->query($query)->fetchAll(\PDO::FETCH_CLASS);
			if (is_array($data)) {
				return (is_int($n)) ? $data[$n] : $data;
			}
			return [];
		}

		/**
		 * Quick & lazy select from table method
		 *
		 * @param string $table    [Name of table]
		 * @param string $these    [Optional column selector(s)]
		 * @return array
		 * @example $pdo->get_table($table)
		 */
		public function get_table($table, $these = '*')
		{
			if ($these !== '*') {
				$these ="`{$these}`";
			}

			return $this->fetch_array(
				"SELECT {$these} FROM {$this->escape($table)}"
			);
		}

		/**
		 * Converts a MySQL query into an HTML <table>
		 * complete with thead and tfoot and optional caption
		 *
		 * @param string $query (MySQL Query)
		 * @return string (HTML <table>)
		 * @example $pdo->sql_table('SELECT * FROM `table`')
		 * @todo Use \DOMDocument & DOMElement
		 */
		public function sql_table($query = null, $caption = null)
		{
			$results = $this->fetch_array($query);

			if (is_array($results) and count($results)) {
				$table = '<table>';
				$thead = '<thead><tr>';
				$tfoot = '<tfoot><tr>';
				$tbody = '<tbody>';

				if (isset($caption)) {
					$table .= "<caption>{$caption}</caption>";
					unset($caption);
				}

				foreach(array_keys(get_object_vars($results[0])) as $th) {
					$thead .= "<th>{$th}</th>";
					$tfoot .= "<th>{$th}</th>";
				}

				$thead .= '</tr></thead>';
				$tfoot .= '</tr></tfoot>';
				$table .= $thead;
				$table .= $tfoot;
				unset($thead);
				unset($tfoot);

				foreach($results as $result) {
					$tbody .= '<tr>';
					foreach(get_object_vars($result) as $td) {
						$tbody .= "<td>{$td}</td>";
					}
					$tbody .= '</tr>';
				}

				$tbody .= '</tbody>';
				$table .= $tbody;
				unset($tbody);
				$table .= '</table>';

				return $table;
			}

			return null;
		}

		/**
		 * Returns a 0 indexed array of column headers for $table
		 *
		 * @param string $table
		 * @return array
		 */
		public function table_headers($table = null)
		{
			$query = "DESCRIBE {$this->escape($table)}";
			$results = $this->pdo->query($query);
			$headers = $results->fetchAll(\PDO::FETCH_COLUMN, 0);
			return $headers;
		}

		/**
		 * For simple Name/Value tables. Gets all name/value pairs. Returns \stdClass object
		 *
		 * @param string $table
		 * @return stdClass
		 */
		public function name_value($table = null)
		{
			$data = $this->fetch_array("
				SELECT
					`name`,
					`value`
				FROM `{$this->escape($table)}`
			");
			$values = new \stdClass();
			foreach($data as $row) {
				$name = trim($row->name);
				$value = trim($row->value);
				$values->$name = $value;
			}
			return $values;
		}

		/**
		 * Removes all entries in a table and resets AUTO_INCREMENT to 1
		 *
		 * @param string $table
		 * @return void
		 */
		public function reset_table($table = null)
		{
			$this->escape($table);
			$this->query("DELETE FROM `{$table}`");
			$this->query("ALTER TABLE `{$table}` AUTO_INCREMENT = 1");
			return $this;
		}

		/**
		 * Simplified method for MySQL "INSERT INTO"s
		 *
		 * @param string $table
		 * @param array $values
		 * @return mixed (result of $this->execute())
		 * @example
		 * $DB->insert_into('users', ['user' => 'user@example.com', 'password' => 'myPassword1'])
		 */
		public function insert_into($table, array $values)
		{
			return $this->prepare(
			"INSERT INTO `{$this->escape($table)}` (
				{$this->columns($values)}
			) VALUES (
				" . join(', ', $this->bind_keys($values)) . '
			)')->bind(
				array_combine($this->prepare_keys($values), array_values($values))
			)->execute();
		}
	}
