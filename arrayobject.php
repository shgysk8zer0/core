<?php
/**
 * @author Chris Zuber
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
 * Provides object-oriented methods to arrays.
 * Allows for cleaner and chained array usage as well as common magic methods
 *
 * @example
 * $arr = new ArrayObject($_REQUEST);
 * $arr->foo = 'bar';
 * $arr->filter('filter_func')->map('map_func')->reduce('reduce_func');
 */
class ArrayObject implements \Iterator, API\Interfaces\Magic_Methods, API\Interfaces\ArrayMethods
{
	use API\Traits\Magic_Methods;
	use API\Traits\Magic\Call;
	use API\Traits\Magic\Iterator;
	use API\Traits\ArrayMethods;

	const MAGIC_PROPERTY = '_data';

	/**
	 * Private storage of data
	 * @var array
	 */
	protected $_data = array();

	/**
	 * Creates a new ArrayObject from an array
	 * @param array $data Optional array to use
	 */
	public function __construct(array $data = array())
	{
		$this->{self::MAGIC_PROPERTY} = $data;
	}

	/**
	 * Static method to create a new instance from an array
	 * @param  array  $array The array to create from
	 * @return ArrayObject   New instance of class
	 */
	final public static function createFromArray(array $array)
	{
		return new self($array);
	}
}
