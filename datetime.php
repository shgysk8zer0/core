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
 * Extends \DateTime class with a __toString method with a public variable
 * $format to use for the format. Also adds several constants to make setting
 * time fomats easier to use and remember.
 * @uses \shgysk8zer0\Core_API\Abstracts\DateTime_Formats
 */
class DateTime extends API\Abstracts\DateTime_Formats
implements \DateTimeInterface, API\Interfaces\String
{
	use API\Traits\DateTime;

	/**
	 * Creates a new instance of DateTime class, with prevention for timezone not set errors
	 *
	 * @param string $time         Any valid date/time string
	 * @param mixed $timezone      String or DateTimeZone object (defaults to system)
	 * @see https://secure.php.net/manual/en/datetime.construct.php
	 */
	public function __construct($time = 'now', $timezone = null)
	{
		parent::__construct($time, $this->_getTimeZone($timezone));
	}
}
