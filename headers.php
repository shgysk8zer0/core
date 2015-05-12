<?php
/**
 * @author Chris Zuber <shgysk8zer0@gmail.com>
 * @package shgysk8zer0\Core
 * @version 1.0.0
 * @link https://developer.github.com/webhooks/
 * @copyright 2015, Chris Zuber
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
 * Setter and unset work with headers client-side
 */
final class Headers implements API\Interfaces\Magic_Methods, \Iterator
{
	use API\Traits\Singleton;
	use API\Traits\Magic\Get;
	use API\Traits\Magic\Is_Set;
	use API\Traits\Magic\Call_Setter;
	use API\Traits\Magic\Iterator;

	const MAGIC_PROPERTY = '_headers';

	/**
	 * Array of headers received
	 * @var array
	 */
	protected $_headers = [];

	/**
	 * Class constructor sets the $headers array
	 *
	 * @param void
	 */
	public function __construct()
	{
		if (function_exists('getallheaders')) {
			$headers = getallheaders();
			$keys = array_keys($headers);
			array_walk($keys, [$this, 'magicPropConvert']);
			$headers = array_combine($keys, array_values($headers));
			$this->{self::MAGIC_PROPERTY} = $headers;
		} else {
			$this->{self::MAGIC_PROPERTY} = array();
		}
	}

	/**
	 * Magic setter for class. Sets headers clint-side
	 *
	 * @param string $key   Header key to set
	 * @param mixed  $value String or array value to set it to
	 * @return void
	 * @example $headers->$key = $value;
	 */
	public function __set($key, $value)
	{
		$this->magicPropConvert($key);
		if (is_array($value) or (is_object($value) and $value = get_object_vars($value))) {
			$value = join('; ', array_map(function($key, $val)
			{
				if (is_string($key)) {
					return "$key=$val";
				} else {
					return "$val";
				}
			}, array_keys($value), array_values($value)));
		}
		header("$key: $value");
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
		$this->magicPropConvert($key);
		header_remove($key);
	}

	/**
	 * Makes headers more consistent
	 *
	 * @param string $prop  Key for header
	 * @return void         Works by reference
	 * @example $this->magicPropConvert($key) // 'content_type' becomes 'Content-Type'
	 */
	protected function magicPropConvert(&$prop)
	{
		$prop = str_replace(' ', '-', ucwords(str_replace(['-', '_'], ' ', strtolower($prop))));
	}
}
