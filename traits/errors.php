<?php
namespace shgysk8zer0\Core\Traits;

trait Errors
{
	public static $error_levels = [];

	/**
	* Converts error constants into PSR-3 defined log levels
	* @param string $e_level E_* error level
	* @return string             Log level as defined by LogLevel constants
	*/
	final public function errorToLogLevel($e_level)
	{
		switch ($e_level) {
			case E_PARSE:
				return \shgysk8zer0\Core\Abstracts\LogLevel::EMERGENCY;
			case E_ERROR:
			case E_CORE_ERROR:
			case E_COMPILE_ERROR:
			case E_USER_ERROR:
				return \shgysk8zer0\Core\Abstracts\LogLevel::ERROR;
			case E_RECOVERABLE_ERROR:
				return \shgysk8zer0\Core\Abstracts\LogLevel::CRITICAL;
			case E_WARNING:
			case E_CORE_WARNING:
			case E_COMPILE_WARNING:
			case E_USER_WARNING:
				return \shgysk8zer0\Core\Abstracts\LogLevel::WARNING;
			case E_NOTICE:
			case E_USER_NOTICE:
				return \shgysk8zer0\Core\Abstracts\LogLevel::NOTICE;
			case E_DEPRECATED:
			case E_USER_DEPRECATED:
				return \shgysk8zer0\Core\Abstracts\LogLevel::INFO;
			case E_STRICT:
				return \shgysk8zer0\Core\Abstracts\LogLevel::DEBUG;
		}
	}

	final protected static function defineErrorLevels()
	{
		if (empty(static::$error_levels)) {
			$consts = get_defined_constants(true)['Core'];
			array_map(function($key, $val)
				{
					if (preg_match('/^E(_[A-Z]+){1,2}$/', $key)) {
						static::$error_levels[$key] = $val;
					}
				},
				array_keys($consts),
				array_values($consts)
			);
		}
	}

	final public static function getErrorFromLevel($code)
	{
		static::defineErrorLevels();
		return array_search($code, static::$error_levels);
	}

	final protected static function errorToException(
		$level,
		$message,
		$file,
		$line,
		$scope
	)
	{
		try {
			throw new \ErrorException($message, 0, $level, $file, $line);
		} catch (\ErrorException $e) {
			return $e;
		}
	}
}
