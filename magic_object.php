<?php
/**
 * @author Chris Zuber <shgysk8zer0@gmail.com>
 * @package shgysk8zer0\Core
 * @version 1.0.0
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
 * Functions as a super-charged \stdClass object (but does not extend it)
 *
 * Improvements include:
 * - Constructor can take arrays and import their data
 * - Has `__call` method, allowing chaining of setting/retrieving data
 * - Can iterate without using `get_object_vars`
 * - Default `__toString` method converts to JSON encoded string
 * - Implements multiple interfaces
 * - Building from multiple traits means this will likely be enhanced over time
 * - Extending this class allows setting `RESTRICT_SETTING` to limit setter
 */
class Magic_Object implements \Iterator, API\Interfaces\Magic_Methods, API\Interfaces\toString
{
	use API\Traits\Magic_Methods;
	use API\Traits\Magic\Call;
	use API\Traits\Magic\Iterator;

	const MAGIC_PROPERTY = 'magic_data';

	/**
	 * Array for data to be used in magic methods
	 * @var array
	 */
	private $magic_data = array();

	/**
	 * Create class, optionally importing data from array(s)/object(s)
	 *
	 * @param mixed ...  Zero or more arrays or objects to create from
	 */
	public function __construct()
	{
		foreach (func_get_args() as $arg) {
			if (
				is_array($arg)
				or (is_object($arg) and $arg = get_object_vars($arg))
			) {
				$this->{$this::MAGIC_PROPERTY} = array_merge(
					$this->{$this::MAGIC_PROPERTY},
					$arg
				);
			}
		}
	}

	/**
	 * Returns a JSON encoded string from MAGIC_PROPERTY when class used as string
	 *
	 * @param void
	 * @return string JSON encoded MAGIC_PROPERTY
	 */
	public function __toString()
	{
		return json_encode($this->{$this::MAGIC_PROPERTY});
	}
}
