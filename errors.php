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
 * Provides easy implementation of error reporting though several methods
 */
final class Errors extends PDO implements API\Interfaces\Errors
{
	use API\Traits\Errors;

	const DEFAULT_METHOD = 'reportError';

	const DATE_FORMAT = 'Y-m-d H:i:s';

	/**
	 * Table to use when reporting errors to database
	 * @var string
	 */
	private $error_table = 'PHP_errors';

	/**
	 * Credentials file for database
	 * @var string
	 */
	public static $con = 'connect.json';

	/**
	 * Array of tables to use when reporting to database
	 * @var array
	 */
	private static $table_cols = [
		'error_type',
		'datetime',
		'error_message',
		'file',
		'line'
	];

	/**
	 * Prepared statement to execute to save errors to database
	 * @var \PDOStatement;
	 */
	private static $error_stm;

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
		if ($method === 'DBError') {
			parent::__construct(static::$con);
			$this::$error_stm = $this->prepare(
				"INSERT INTO `{$this->error_table}` (
					{$this->columns(array_flip(static::$table_cols))}
				) VALUES (
					:" . join(', :', static::$table_cols) . "
				);"
			);
		}

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
	public static function reportError(
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
	 * Saves error to database
	 *
	 * @param int    $level   Any of the error levels (E_*)
	 * @param string $message Message given with the error
	 * @param string $file    File generating the error
	 * @param itn    $line    Line on which the error occured
	 * @param array  $context All set variables in scope
	 * @return void
	 */
	public static function DBError(
		$level,
		$message,
		$file,
		$line,
		array $context = array()
	)
	{
		static::$error_stm->execute(array_combine(
			static::$table_cols,
			[$level, date(self::DATE_FORMAT), $message, $file, $line]
		));
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
	static public function AJAXError(
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
	public static function logError($level, $message, $file, $line, $scope)
	{
		file_put_contents(
			BASE . DIRECTORY_SEPARATOR . static::$LOG_DIR . DIRECTORY_SEPARATOR . static::$LOG_FILE,
			static::errorToException($level, $message, $file, $line, $scope) . PHP_EOL,
			LOCK_EX | FILE_APPEND
		);
	}
}
