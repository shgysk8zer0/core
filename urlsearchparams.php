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
 * Class for easily building and altering URLs.
 */
final class URLSearchParams implements API\Interfaces\String, \Iterator
{
	use API\Traits\Magic\Iterator;

	const MAGIC_PROPERTY = '_params';
	protected $_params = array();

	/**
	 * Creates a new instance of URLSearchParams
	 *
	 * @param string $params Query string for a URL
	 */
	public function __construct($params = '')
	{
		parse_str($params, $this->{self::MAGIC_PROPERTY});
	}

	/**
	 * Converts $_params into an HTTP query (without the leading "?")
	 *
	 * @return string "foo=bar&name=John+Smith"
	 */
	public function __toString()
	{
		return http_build_query($this->{self::MAGIC_PROPERTY});
	}

	/**
	 * Magic setter method
	 *
	 * @param string $key   Paramater name
	 * @param mixed  $value Paramater value
	 */
	public function __set($key, $value)
	{
		$this->{self::MAGIC_PROPERTY}[$key] = $value;
	}

	/**
	 * Magic getter method
	 *
	 * @param  string $key Paramater name
	 * @return string      Since these are search params, it must be a string
	 */
	public function __get($key)
	{
		return isset($this->{$key}) ? $this->{self::MAGIC_PROPERTY}[$key] : null;
	}

	/**
	 * Magic isset method
	 *
	 * @param  string  $key Paramater name
	 * @return boolean
	 */
	public function __isset($key)
	{
		return array_key_exists($key, $this->{self::MAGIC_PROPERTY});
	}

	/**
	 * Magic unset method
	 *
	 * @param string $key Paramater name
	 */
	public function __unset($key)
	{
		unset($this->{self::MAGIC_PROPERTY}[$key]);
	}
}
