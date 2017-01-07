<?php
/**
 * @author Chris Zuber
 * @package shgysk8zer0\Core
 * @version 1.0.0
 * @copyright 2017, Chris Zuber
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
 * Class to simplify working with data from arrays, such as $_REQUEST
 */
class FormData extends \ArrayObject
{
	/**
	 * Creates a new instance of class
	 * @param Array $inputs Data, such as from $_REQUEST
	 */
	public function __construct(Array $inputs)
	{
		parent::__construct(static::_convert($inputs), self::ARRAY_AS_PROPS);
	}

	/**
	 * Returns a string in the form of an HTTP query
	 * @param void
	 * @return string (foo=bar&...)
	 */
	public function __toString()
	{
		return http_build_query($this->getArrayCopy());
	}

	public function __invoke(Array $def, $add_empty = false)
	{
		return filter_var_array($this->getArrayCopy(), $defs, $add_empty);
	}

	/**
	 * Returns the request method
	 * @param void
	 * @return string POST|GET|HEAD....
	 */
	public function getMethod()
	{
		return $_SERVER['REQUEST_METHOD'];
	}

	/**
	 * Converts all non-numeric indexed arrays into `\ArrayObject`s
	 * @param  Array  $arr      Source array
	 * @return ArrayObject      Array converted into an `\ArrayObject`
	 */
	private static function _convert(Array $arr)
	{
		$arr = new \ArrayObject($arr, self::ARRAY_AS_PROPS);

		foreach (get_object_vars($arr) as $key => $value) {
			if (is_array($value) and is_string($key)) {
				$arr->{$key} = call_user_func(__METHOD__, $value);
			}
		}
		return $arr;
	}
}
