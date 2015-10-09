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

/**
 * Class to easy work with query strings
 */
final class SearchParams extends \stdClass implements \shgysk8zer0\Core_API\Interfaces\String
{
	/**
	 * Create instance from optional URL / query string
	 *
	 * @param string $url  Optional URL or query string to build from
	 */
	public function __construct($url = null)
	{
		if (is_string($url)) {
			$url = parse_url($url);
			if (array_key_exists('query', $url)) {
				$query = array();
				parse_str($url['query'], $query);
				foreach ($query as $key => $value) {
					$this->$key = $value;
				}
			}
		}
	}

	/**
	 * Returns a string created for search paramaters
	 *
	 * @return string ?foo=bar
	 */
	public function __toString()
	{
		return '?' . http_build_query($this);
	}
}
