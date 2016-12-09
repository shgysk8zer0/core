<?php
/**
 * @author Chris Zuber <shgysk8zer0@gmail.com>
 * @package shgysk8zer0\Core
 * @version 01.0.0
 * @copyright 2016, Chris Zuber
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
namespace shgysk8zer0\Core;

/**
 * Creates a properly formatted DSN string for creating PDO instances
 */
final class DSN
{
	/**
	 * Default connection paramaters
	 * @var array
	 */
	const DEFAULTS = array(
		'host' => 'localhost',
		'type' => 'mysql',
		'port' => 3306,
	);

	/**
	 * Format to use with `sprintf` when converting to string
	 * @var string
	 */
	const FORMAT = '%s:dbname=%s;host=%s;port=%d';

	const DEFAULT_DB_CREDS = 'connect.json';

	/**
	 * Array containing database credentials
	 * @var array
	 */
	private $_creds = array(
		'user'     => null,
		'password' => null,
		'host'     => null,
		'database' => null,
		'type'     => null,
		'port'     => null,
	);

	private static $instances = array();

	/**
	 * Creates a new instance from an array of connection credentials
	 *
	 * @param Array $con ['user', 'password', ...]
	 */
	public function __construct(Array $con)
	{
		$this->_parseCreds($con);
		if ($this->_isValidCreds($con)) {
			$this->_creds = $con;
		} else {
			throw new \InvalidArgumentException('$con is not valid for creating a DSN string.');
		}
	}

	/**
	 * Checks if a property is set in credentials array
	 * @param  string  $prop Property to check
	 * @return boolean       Whether or not it is set
	 */
	public function __isset($prop)
	{
		return isset($this->_creds[$prop]);
	}

	/**
	 * Returns a database credentials property
	 *
	 * @param  string $prop Credentials property to get
	 * @return mixed        Value of the property
	 */
	public function __get($prop)
	{
		if ($this->__isset($prop)) {
			return $this->_creds[$prop];
		}
	}

	/**
	 * Returns DSN string
	 *
	 * @param void
	 * @return string '%s:dbname=%s;host=%s;port=%d'
	 */
	public function __toString()
	{
		return sprintf(
			self::FORMAT,
			$this->type,
			$this->database,
			$this->host,
			$this->port
		);
	}

	/**
	 * Private function to parse database credentials
	 *
	 * @param  Array  $creds Array containing credentials
	 * @return void
	 */
	private function _parseCreds(Array &$creds)
	{
		if (array_key_exists('user', $creds) and ! array_key_exists('database', $creds)) {
			$creds['database'] = $creds['user'];
		}
		$creds = array_merge(self::DEFAULTS, $creds);
	}

	/**
	 * Private setter method
	 *
	 * @param string $prop  Property name
	 * @param mixed  $value Property value
	 */
	private function _set($prop, $value)
	{
		if (array_key_exists($prop, $this->_creds)) {
			$this->_creds[$prop] = $value;
		} else {
			trigger_error(sprintf('Invalid property, "%s."', $prop));
		}
	}

	/**
	 * Checks if credentials array contains all required entries/keys
	 * @param  Array   $creds Array to check
	 * @return boolean        Whether or not it contains all of the required keys
	 */
	private function _isValidCreds(Array $creds)
	{
		foreach(array_keys($this->_creds) as $key) {
			if (! array_key_exists($key, $creds)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Static method to construct from a JSON file
	 *
	 * @param  string $fname Name of file to parse from
	 * @return DSN           An anstance of self
	 */
	public static function fromJSON(String $fname = self::DEFAULT_DB_CREDS)
	{
		$ext = pathinfo($fname, PATHINFO_EXTENSION);
		if (empty($ext)) {
			$fname = "{$fname}.json";
		}
		$key = basename($fname);

		if (! array_key_exists($key, static::$instances)) {
			if (@file_exists($fname)) {
				static::$instances[$key] = new self(json_decode(file_get_contents($fname, true), true));
			} else {
				throw new \InvalidArgumentException("{$fname} not found.");
			}
		}
		return static::$instances[$key];
	}
}
