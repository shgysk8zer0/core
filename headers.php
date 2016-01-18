<?php
/**
 * @author Chris Zuber <shgysk8zer0@gmail.com>
 * @package shgysk8zer0\Core
 * @version 1.0.0
 * @link https://developer.github.com/webhooks/
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
 * Provides consistent and accessible methods for getting and checking headers.
 * Setter and unset work with response headers.
 * Getter and isset work with request headers.
 */
final class Headers implements API\Interfaces\Magic_Methods
{
	use API\Traits\Singleton;
	use API\Traits\GetInstance;
	use API\Traits\Magic\Call_Setter;
	use API\Traits\Headers;

	/**
	 * Class constructor sets the $headers array
	 *
	 * @param void
	 */
	public function __construct()
	{
		$this->_readHeaders();
	}

	/**
	 * Magic setter for class. Sets headers client-side
	 *
	 * @param string $key   Header key to set
	 * @param mixed  $value String or array value to set it to
	 * @return void
	 * @example $headers->$key = $value;
	 */
	public function __set($key, $value)
	{
		static::setHeader($key, $value);
	}

	/**
	 * Get a request header value by name
	 *
	 * @param  string $key Name of the header
	 * @return string      It's value
	 */
	public function __get($key)
	{
		return static::getHeader($key);
	}

	/**
	 * Check if a request header was sent
	 *
	 * @param  string  $key Name of the header
	 * @return boolean      If it was sent
	 */
	public function __isset($key)
	{
		return static::hasHeader($key);
	}

	/**
	 * Magic method to unset/remove a header client-side
	 *
	 * @param string $key The header key to remove
	 * @return void
	 * @example unset($headers->$key);
	 */
	public function __unset($key)
	{
		static::removeHeader($key);
	}
}
