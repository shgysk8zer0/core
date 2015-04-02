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
 * Very simple class for timing things using microtime.
 * @example $timer = new \shgysk8zer0\Core\Time;
 * // ... Do some stuff
 * echo "Took $timer seconds.";
 */
class Timer
{
	use \shgysk8zer0\Core_API\Traits\Singleton;
	/**
	 * Unix timestamp with microseconds
	 * @var float
	 */
	protected $timer_start = 0;

	/**
	 * Creates Timer instance and sets $timer_start to current microtime
	 *
	 * @param void
	 */
	public function __construct()
	{
		$this->timer_start = microtime(true);
	}

	/**
	 * Called whenever class object used as a string
	 *
	 * @return string Difference between $timer_start & current microtime
	 */
	public function __toString()
	{
		return (string) (microtime(true) - $this->timer_start);
	}
}
