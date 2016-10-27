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
namespace shgysk8zer0\ShareAPI;

/**
 * Class to create URL query strings / search paramaters in an object-oriented way.
 */
class URLSearchParams extends \ArrayObject implements \shgysk8zer0\Core_API\Interfaces\toString
{
	/**
	 * Create a new instance of URLSearchParams from string, array, or object
	 * @param mixed $params Initial value for search paramaters
	 */
	public function __construct($params = null)
	{
		if (is_string($params)) {
			parse_str(trim($paramsm, '?'), $params);
			parent::__construct($params);
		} else {
			parent::__construct($params);
		}
	}

	/**
	 * Checks if a paramater is set
	 *
	 * @param  string  $param Query paramater to check for
	 * @return bool
	 */
	public function __isset($param)
	{
		return array_key_exists($param, $this);
	}

	/**
	 * Removes a paramater
	 *
	 * @param string $param Pramater to remove
	 * @return void
	 */
	public function __unset($param)
	{
		unset($this[$param]);
	}

	/**
	 * Sets a URL paramater
	 *
	 * @param string $param Paramater to set
	 * @param mixed  $value Value to set it to
	 * @return void
	 */
	public function __set($param, $value)
	{
		$this[$param] = $value;
	}

	/**
	 * Gets the value of a paramater
	 *
	 * @param  string $param The paramater to check for
	 * @return mixed         Its value, if any
	 */
	public function __get($param)
	{
		if ($this->__isset($param)) {
			return $this[$param];
		}
	}

	/**
	 * Returns a URL query string
	 *
	 * @param void
	 * @return string The search params / query string
	 */
	public function __toString()
	{
		return http_build_query($this);
	}
}
