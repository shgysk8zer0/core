<?php
namespace shgysk8zer0\Core\Abstracts;

abstract class Errors
{
	/**
	 * Converts error constants into PSR-3 defined log levels
	 * @param string $e_level E_* error level
	 * @return string             Log level as defined by LogLevel constants
	 */
	final public function errorToLogLevel($e_level)
	{
		switch ($e_level) {
			case E_PARSE:
				return LogLevel::EMERGENCY;
			case E_ERROR:
			case E_CORE_ERROR:
			case E_COMPILE_ERROR:
			case E_USER_ERROR:
				return LogLevel::ERROR;
			case E_RECOVERABLE_ERROR:
				return LogLevel::CRITICAL;
			case E_WARNING:
			case E_CORE_WARNING:
			case E_COMPILE_WARNING:
			case E_USER_WARNING:
				return LogLevel::WARNING;
			case E_NOTICE:
			case E_USER_NOTICE:
				return LogLevel::NOTICE;
			case E_DEPRECATED:
			case E_USER_DEPRECATED:
				return LogLevel::INFO;
			case E_STRICT:
				return LogLevel::DEBUG;
		}
	}
}
