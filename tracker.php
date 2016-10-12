<?php
/**
 * @author Chris Zuber <shgysk8zer0@gmail.com>
 * @package shgysk8zer0\Core
 * @version 1.0.0
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

use \shgysk8zer0\Core_API as API;

/**
 * Class for logging UA-String, Remote IP address, URI, access time, and visit
 * count into a database.
 * @uses PDO
 */
final class Tracker implements API\Interfaces\toString
{
	const MAGIC_PROPERTY = '_data';

	const SQL = "INSERT INTO `%TABLE%` (
		`ip`,
		`ua`,
		`uri`
	) VALUES (
		:ip,
		:ua,
		:uri
	) ON DUPLICATE KEY UPDATE `count` = `count` + 1";

	/**
	 * Array of data to be inserting into the database
	 * @var array
	 */
	private $_data = array(
		'ip' => '',
		'ua' => '',
		'uri' => ''
	);

	/**
	 * Creates a new instance of the class. Sets and stores data in database
	 *
	 * @param mixed  $con   Database credentials
	 * @param string $table Table in database to use (Defaults to server name)
	 */
	public function __construct($con = null, $table = null)
	{
		$this->{self::MAGIC_PROPERTY}['ip'] = $_SERVER['REMOTE_ADDR'];
		$this->{self::MAGIC_PROPERTY}['ua'] = $_SERVER['HTTP_USER_AGENT'];
		$this->{self::MAGIC_PROPERTY}['uri'] = $_SERVER['REQUEST_URI'];
		$this->_storeData($con, is_null($table) ? $_SERVER['SERVER_NAME'] : $table);
	}

	/**
	 * Checks if a key exists in data array
	 *
	 * @param  string  $prop Property to check if exists
	 * @return bool
	 */
	public function __isset($prop)
	{
		return array_key_exists(strtolower($prop), $this->{self::MAGIC_PROPERTY});
	}

	/**
	 * Gets a property from the data array
	 *
	 * @param  string $prop Property to get
	 * @return mixed        Its value
	 */
	public function __get($prop)
	{
		$prop = strtolower($prop);
		if ($this->__isset($prop)) {
			return $this->{self::MAGIC_PROPERTY}[$prop];
		}
	}

	/**
	 * Returns a JSON encoded string of data array
	 * @return string Data array, JSON encoded
	 */
	public function __toString()
	{
		return json_encode($this->{self::MAGIC_PROPERTY});
	}

	/**
	 * Returns the data array for `print_r` and `var_dump`
	 *
	 * @return array The data array
	 */
	public function __debugInfo()
	{
		return $this->{self::MAGIC_PROPERTY};
	}

	/**
	 * Stores data array to database
	 *
	 * @param  mixed  $con   Database credentials
	 * @param  string $table Table to use
	 * @return void
	 */
	private function _storeData($con, $table)
	{
		$pdo = PDO::load($con);
		$stm = $pdo->prepare(str_replace('%TABLE%', $table, self::SQL));
		$stm->execute($this->{self::MAGIC_PROPERTY});
	}
}
