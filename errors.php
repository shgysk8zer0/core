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
 * Provides easy implementation of error reporting though several methods
 */
class Errors implements \shgysk8zer0\Core_API\Interfaces\Errors
{
	use \shgysk8zer0\Core_API\Traits\Errors;

	const DEFAULT_METHOD = 'reportError';

	/**
	 * Directory to use for logError
	 * @var string
	 */
	public static $LOG_DIR = 'logs';

	/**
	 * Filename to use for logError
	 * @var string
	 */
	public static $LOG_FILE = 'errors.log';

	/**
	 * Sets $this::{$method} as error handler
	 *
	 * @param string $method Name of method to call on errors
	 * @param int    $level  E_* constant(s)
	 */
	public function __construct($method = self::DEFAULT_METHOD, $level = null)
	{
		if (! is_int($level)) {
			$level = error_reporting();
		}
		if ( ! is_string($method) or ! method_exists($this, $method)) {
			$method = self::DEFAULT_METHOD;
		}
		set_error_handler([$this, $method], $level);
	}

	/**
	 * Prints an error
	 *
	 * @param int    $level   Any of the error levels (E_*)
	 * @param string $message Message given with the error
	 * @param string $file    File generating the error
	 * @param int    $line    Line on which the error occured
	 * @param array  $context All set variables in scope
	 * @return void
	 */
	final public static function reportError(
		$level,
		$message,
		$file,
		$line,
		array $context = array()
	)
	{
		echo static::errorToException($level, $message, $file, $line, $context) . PHP_EOL;
	}

	/**
	 * Sends an error to console.error
	 *
	 * @param int    $level   Any of the error levels (E_*)
	 * @param string $message Message given with the error
	 * @param string $file    File generating the error
	 * @param int    $line    Line on which the error occured
	 * @param array  $context All set variables in scope
	 * @return void
	 */
	final static public function AJAXError(
		$level,
		$message,
		$file,
		$line,
		array $context = array()
	)
	{
		header('Content-Type: application/json');
		$e = static::errorToException($level, $message, $file, $line, $context);
		exit(json_encode([
			'error' => [
				'level' => static::errorLevelAsString($level),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'message' => $e->getMessage(),
				'trace' => $e->getTraceAsString(),
				'class' => get_class($e)
			]
		]));
	}

	/**
	 * Saves an error to file
	 *
	 * @param int    $level   Any of the error levels (E_*)
	 * @param string $message Message given with the error
	 * @param string $file    File generating the error
	 * @param int    $line    Line on which the error occured
	 * @param array  $context All set variables in scope
	 * @return void
	 */
	final public static function logError($level, $message, $file, $line, $scope)
	{
		file_put_contents(
			BASE . DIRECTORY_SEPARATOR . static::$LOG_DIR . DIRECTORY_SEPARATOR . static::$LOG_FILE,
			static::errorToException($level, $message, $file, $line, $scope) . PHP_EOL,
			LOCK_EX | FILE_APPEND
		);
	}
}
