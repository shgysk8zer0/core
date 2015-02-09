<?php

	namespace shgysk8zer0\Core\Interfaces;
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
	interface PDO
	{
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
		public static function load($con = 'connect');

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
		public function __construct($con = 'connect');

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

		public function prepare($query);

		/**
		 * Need PDO method to close database connection
		 *
		 * @param void
		 * @return void
		 * @todo Make it actually close the connection
		 * @todo Extend to __destruct
		 */
		//public function close();

		/**
		 * Get the results of a SQL query
		 *
		 * @param string $query
		 * @return mixed
		 */
		public function query($query);

		/**
		 * Return the results of a query as an associative array
		 *
		 * @param string $query
		 * @param int $n
		 * @return array
		 */
		public function fetch_array($query = null, $n = null);

		/**
		 * Quick & lazy select from table method
		 *
		 * @param string $table    [Name of table]
		 * @param string $these    [Optional column selector(s)]
		 * @return array
		 * @example $pdo->get_table($table)
		 */
		//public function get_table($table, $these = '*');

		/**
		 * Converts a MySQL query into an HTML <table>
		 * complete with thead and tfoot and optional caption
		 *
		 * @param string $query (MySQL Query)
		 * @return string (HTML <table>)
		 * @example $pdo->sql_table('SELECT * FROM `table`')
		 * @todo Use \DOMDocument & DOMElement
		 */
		//public function sql_table($query = null, $caption = null);

		/**
		 * Returns a 0 indexed array of column headers for $table
		 *
		 * @param string $table
		 * @return array
		 */
		//public function table_headers($table = null);

		/**
		 * For simple Name/Value tables. Gets all name/value pairs. Returns \stdClass object
		 *
		 * @param string $table
		 * @return stdClass
		 */
		public function name_value($table = null);

		/**
		 * Removes all entries in a table and resets AUTO_INCREMENT to 1
		 *
		 * @param string $table
		 * @return void
		 */
		public function reset_table($table = null);

		/**
		 * Simplified method for MySQL "INSERT INTO"s
		 *
		 * @param string $table
		 * @param array $values
		 * @return mixed (result of $this->execute())
		 * @example
		 * $DB->insert_into('users', ['user' => 'user@example.com', 'password' => 'myPassword1'])
		 */
		//public function insert_into($table, array $values);
	}
