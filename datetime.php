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
 * Extends \DateTime class with a __toString method with a public variable
 * $format to use for the format. Also adds several constants to make setting
 * time fomats easier to use and remember.
 * @see http://php.net/manual/en/function.date.php
 * @uses \DateTime
 */
class DateTime extends \DateTime
{

	/**
	 * Common textual date components
	 */
	const LONG_TEXT_DAY_OF_WEEK    = 'D';
	const SHORT_TEXT_DAY_OF_WEEK   = 'l';
	const DAY                      = 'l';
	const LONG_TEXT_MONTH          = 'F';
	const SHORT_TEXT_MONTH         = 'M';
	const TEXT_MONTH               = 'F';
	const DAY_SUFFIX               = 'S';
	const AMPM_UPPER               = 'A';
	const AMPM_LOWER               = 'a';
	const AMPM                     = 'A';

	/**
	 * Common numeric date components
	 */
	const LONG_YEAR                = 'Y';
	const SHORT_YEAR               = 'y';
	const ISO_YEAR                 = 'o';
	const YEAR                     = 'Y';
	const LONG_MONTH               = 'm';
	const SHORT_MONTH              = 'n';
	const MONTH                    = 'm';
	const LONG_DAY_OF_MONTH        = 'd';
	const SHORT_DAY_OF_MONTH       = 'j';
	const DAY_OF_MONTH             = 'd';
	const HOUR_24_LONG             = 'H';
	const HOUR_24_SHORT            = 'G';
	const HOUR_12_LONG             = 'h';
	const HOUR_12_SHORT            = 'g';
	const HOUR_24                  = 'H';
	const HOUR_12                  = 'g';
	const LONG_HOUR                = 'H';
	const SHORT_HOUR               = 'h';
	const HOUR                     = 'h';
	const MINUTES                  = 'i';
	const SECONDS                  = 's';

	/**
	 * Less common parts
	 */
	const DAY_OF_YEAR              = 'z';
	const WEEK_OF_YEAR             = 'W';
	const WEEK                     = 'W';
	const DAYS_IN_MONTH            = 't';
	const MICROSECONDS             = 'u';
	const SWATCH                   = 'B';

	/**
	 * Timezones
	 */
	const TIMEZONE                 = 'e';
	const TIMEZONE_ABV             = 'T';
	const TIMEZONE_OFFSET          = 'Z';
	const GMT_OFFSET               = 'O';
	const GMT_OFFSET_FORMATTED     = 'P';

	/**
	 * Tests. 1 for true, 0 for false
	 */
	const IS_LEAP_YEAR             = 'L';
	const IS_DAYLIGHT_SAVINGS_TIME = 'I';

	/**
	 * Full "human" formats
	 */
	const LONG                     = 'l, F jS Y h:i A';
	const SHORT                    = 'D, M jS Y h:i A';

	/**
	 * Format to use when using __toString
	 * @var string
	 */
	public $format = self::W3C;

	/**
	 * Called when class is used as a string and returns date formated as string
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->format($this->format);
	}
}
