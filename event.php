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
 * Provides a basic & magic implementation of Core_API\Abstracts\Events
 */
final class Event extends API\Abstracts\Events implements API\Interfaces\Magic_Events
{
	/**
	 * Creates/registers a new Event
	 *
	 * @param string   $event    Name of the event
	 * @param Callable $callback Callback to call when triggered
	 * @example new Event('myEvent', function() use() {});
	 */
	public function __construct($event, Callable $callback)
	{
		static::registerEvent($event, $callback);
	}

	/**
	 * Calls the event callback with the given args
	 *
	 * @param string $event   Name of the event
	 * @param array  $context Arguments to pass to the callback function
	 * @return void
	 * @example Event::myEvent($arg1, ..., $argn);
	 */
	final public static function __callStatic($event, array $context = array())
	{
		static::triggerEvent($event, $context);
	}
}
