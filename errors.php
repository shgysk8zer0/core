<?php
namespace shgysk8zer0\Core;

class Errors
{
	use \shgysk8zer0\Core\Traits\Errors;

	const DEFAULT_METHOD = 'printError';

	public static $LOG_DIR = 'logs',
		$LOG_FILE = 'errors.log';

	/**
	 * Sets $this::{$method} as error handler
	 *
	 * @param string $method Name of method to call on errors
	 * @param int    $level  E_* constant(s)
	 */
	public function __construct($method = 'printError', $level = null)
	{
		if (! is_int($level)) {
			$level = error_reporting();
		}
		if ( ! is_string($method) or ! method_exists($this, $method)) {
			$method = $this::DEFAULT_METHOD;
		}
		set_error_handler([$this, $method], $level);
	}

	final public static function printError($level, $message, $file, $line, $scope)
	{
		echo static::errorToException($level, $message, $file, $line, $scope) . PHP_EOL;
	}

	final public static function logError($level, $message, $file, $line, $scope)
	{
		file_put_contents(
			BASE . DIRECTORY_SEPARATOR . static::$LOG_DIR . DIRECTORY_SEPARATOR . static::$LOG_FILE,
			static::errorToException($level, $message, $file, $line, $scope) . PHP_EOL,
			LOCK_EX | FILE_APPEND
		);
	}

	final static public function trigger($message, $level = E_USER_NOTICE)
	{
		trigger_error($message, $level);
	}
}
