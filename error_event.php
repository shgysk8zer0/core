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
 * Create callbacks to be called as events for each type of error level/event.
 * Allows multiple or no callbacks to be registered for each E_*. Any and all
 * registered callbacks are called when an error of that type occurs.
 */
final class Error_Event
{
	use API\Traits\Errors;

	const ERROR_HANDLER = 'reportError';

	/**
	 * Array of callbacks for each error level/event
	 * @var array
	 */
	private static $error_handlers = [];

	/**
	 * Creates instance and sets up error handling.
	 *
	 * @param void
	 */
	public function __construct()
	{
		set_error_handler([$this, self::ERROR_HANDLER], E_ALL);
	}

	/**
	 * Register a callback for an error level (notice, warning, etc)
	 *
	 * @param string   $level    Case insensitive E_* constant, without the "E_"
	 * @param Callable $callback Callback to be registered for the event/level
	 * @return void
	 */
	public function __set($level, Callable $callback)
	{
		$level = 'E_' . strtoupper($level);
		static::$error_handlers[$level][] = $callback;
	}

	/**
	 * Static method called when an error is triggered
	 * Calls the handler, if any, for the error level/event
	 *
	 * @param int    $level   Int value of E_* constant error level
	 * @param string $message Error description given
	 * @param string $file    The file which the error occured in
	 * @param int    $line    The line in $file the error occured on
	 * @param array  $scope   Variables set in scope when error occured
	 * @return mixed
	 */
	public static function reportError(
		$level,
		$message,
		$file,
		$line,
		array $scope = array()
	)
	{
		$error_exception = static::errorToException($level, $message, $file, $line);
		$level = static::errorLevelAsString($level);
		if (array_key_exists($level, static::$error_handlers)) {
			array_map(function($handler) use ($error_exception)
			{
				call_user_func($handler, $error_exception);
			}, static::$error_handlers[$level]);
			return true;
		} else {
			return false;
		}

	}
}
