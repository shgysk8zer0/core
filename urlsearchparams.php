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
			parse_str(trim($params, '?'), $params);
		}
		parent::__construct($params, self::ARRAY_AS_PROPS);
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
