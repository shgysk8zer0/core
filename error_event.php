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
 *
 */
final class Error_Event implements API\Interfaces\Errors
{
	use API\Traits\Events;
	use API\Traits\Errors;

	const ERROR_HANDLER = 'reportError';

	/**
	 * [__construct description]
	 *
	 * @param void
	 */
	public function __construct()
	{
		set_error_handler([$this, self::ERROR_HANDLER], E_ALL);
	}

	public function __set($event, Callable $callback)
	{
		$event = 'E_' . strtoupper($event);
		static::registerEvent($event, $callback);
	}

	public static function reportError(
		$level,
		$message,
		$file,
		$line,
		array $scope = array()
	)
	{
		$error_exception = static::errorToException($level, $message, $file, $line);
		static::triggerEvent(
			static::errorLevelAsString($level),
			[
				'message' => $error_exception->getMessage(),
				'code' => $error_exception->getCode(),
				'file' => $error_exception->getFile(),
				'line' => $error_exception->getLine(),
				'trace' => $error_exception->getTrace(),
				'scope' => $scope
			]
		);
	}
}
