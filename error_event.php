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
	const PREFIX        = 'E_';
	const E_LEVEL       = E_ALL;

	/**
	 * Array of callbacks for each error level/event
	 * @var array
	 */
	private static $error_handlers = [];

	/**
	 * Default error handler... Always called if set
	 * @var Callable
	 */
	private static $default_handler = null;

	/**
	 * Creates instance and sets up error handling.
	 *
	 * @param string $error_method    Method to call when error reported
	 * @param int    $lvl             E_* level to set as handler for
	 * @param array  $callbacks       Array of callbacks to register
	 * @param bool   $disable_default
	 */
	public function __construct(
		$error_method    = self::ERROR_HANDLER,
		$lvl             = self::E_LEVEL,
		array $callbacks = array(),
		$disable_default = true
	)
	{
		if (! is_int($lvl)) {
			$lvl = self::E_LEVEL;
		}
		if (is_string($error_method) and method_exists(__CLASS__, $error_method)) {
			set_error_handler([__CLASS__, $error_method], $lvl);
		} else {
			set_error_handler([__CLASS__, self::ERROR_HANDLER], $lvl);
		}

		if ($disable_default) {
			error_reporting(0);
		}

		$callbacks = array_filter($callbacks, 'is_callable');
		array_map([$this, '__set'], array_keys($callbacks), array_values($callbacks));
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
		$this->_ELevel($level);
		static::$error_handlers[$level][] = $callback;
	}

	/**
	 * Chainable setter capable of setting multiple callbacks per event
	 *
	 * @param  string $level String version of E_* constants (E_ is optional)
	 * @param  array  $args  Array of callbacks given to the function call
	 * @return self
	 * @example $this->fatal($callback1, ...)->...
	 * @example $this->E_FATAL($callback1, ...)->...
	 */
	public function __call($level, array $args = array())
	{
		$args = array_filter($args, 'is_callable');
		$this->ELevel($level);
		array_map(
			[$this, '__set'],
			array_pad(array(), count($args), $level),
			array_values($args)
		);
		return $this;
	}

	/**
	 * Magic method to set default callback for error handler
	 *
	 * @param  Callable $callback The default callback for all errors
	 * @return void
	 * @example $errors('callback')
	 * @example $errors(function(\ErrorException) use ($PDO){};);
	 */
	public function __invoke(Callable $callback)
	{
		static::$default_handler = $callback;
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
		if (is_callable(static::$default_handler)) {
			call_user_func(static::$default_handler, $error_exception);
		}
		if (array_key_exists($level, static::$error_handlers)) {
			array_map(
				function($handler) use ($error_exception)
				{
					call_user_func($handler, $error_exception);
				},
				static::$error_handlers[$level]
			);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Converts strings into E_* constant names (does not verify them)
	 *
	 * @param string $lvl E_* constant level as string (E_ prefix optional)
	 * @return void
	 * @example $this->ELevel($lvl = 'fatal'); // Converts $lvl to 'E_FATAL'
	 */
	private function _ELevel(&$lvl)
	{
		if (is_string($lvl)) {
			$lvl = strtoupper($lvl);
			if (substr($lvl, 0, strlen(self::PREFIX)) !== self::PREFIX) {
				$lvl = self::PREFIX . $lvl;
			}
		}
	}
}
