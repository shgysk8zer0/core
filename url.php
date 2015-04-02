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
 * Class for easily building and altering URLs.
 */
final class URL
{
	use API\Traits\Singleton;
	use API\Traits\URL;
	use API\Traits\Magic_Methods;
	use API\Traits\Magic\Call;

	const MAGIC_PROPERTY   = 'url_data';
	const RESTRICT_SETTING = true;

	/**
	 * Array of components to retrieve for URL (null means all)
	 * @var array
	 */
	private $components = array();

	/**
	 * Creates a new URL instance using either computed or given URL
	 *
	 * @param string $url Optional URL to parse
	 */
	public function __construct($url = null)
	{
		$this->parseURL($url);
	}

	/**
	 * Set components to retrieve when getting URL as string
	 *
	 * @param string ...
	 * @return self
	 */
	public function getComponents()
	{
		$this->components = array_filter(
			func_get_args(),
			function($comp)
			{
				return array_key_exists($comp, $this->{self::MAGIC_PROPERTY});
			}
		);
		return $this;
	}

	/**
	 * Convert parsed URL to string, limiting by any set $components
	 *
	 * @param void
	 * @return string URL components as string
	 */
	public function __toString()
	{
		return empty($this->components)
			? $this->URLToString(array_keys($this->{self::MAGIC_PROPERTY}))
			: $this->URLToString($this->components);
	}
}
