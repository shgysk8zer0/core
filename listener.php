<?php
/**
 * @author Chris Zuber <shgysk8zer0@gmail.com>
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

class Listener
{
	/**
	 * Array of callbacks [event => callback]
	 * @var array
	 */
	private static $events = array();

	/**
	 * Create a new instance, with an optional event/callback
	 * @param string`  $event    Event name
	 * @param Callable $callback Callback for event
	 */
	public function __construct($event = null, Callable $callback = null)
	{
		if (isset($event, $callback)) {
			$this->{$event} = $callback;
		}
	}

	/**
	 * Add a new handler for an event
	 * @param string   $event    Event name
	 * @param Callable $callback event callback
	 */
	public function __set($event, Callable $callback)
	{
		if (! isset($this->{$event})) {
			static::$events[$event] = new Subject($event);
		}
		static::$events[$event]->attach(new Observer($callback));
	}

	/**
	 * Returns the callbacks function for an event
	 * @param  string    $event Event name
	 * @return Callable        Event's callbacks
	 */
	public function __get($event)
	{
		return isset($this->{$event}) ? static::$events[$event] : null;
	}

	/**
	 * Checks if an event handler is set
	 * @param  string  $event Event to check for
	 * @return boolean        Whether or not it is set
	 */
	public function __isset($event)
	{
		return array_key_exists($event, static::$events);
	}

	/**
	 * Removes all listeners for event
	 * @param string $event Event name
	 */
	public function __unset($event)
	{
		unset(static::$events[$event]);
	}

	/**
	 * Calls an event's callbacks
	 * @param  string $event Event name
	 * @param  Array  $args  Array of arguments
	 * @return void
	 */
	public function __call($event, Array $args)
	{
		$this->{$event}->args = $args;
		$this->{$event}->notify();
	}

	/**
	 * Static method for triggering an event
	 * @param  string $event Event to trigger
	 * @param  Array  $args  Array of arguments to pass to callback
	 * @return void
	 */
	public static function __callStatic($event, Array $args)
	{
		$events = new self();
		if (isset($events->{$event})) {
			$events->{$event}(...$args);
		}
		return true;
	}
}
