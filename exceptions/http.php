<?php
/**
 * @author Chris Zuber <shgysk8zer0@gmail.com>
 * @package shgysk8zer0\Core
 * @subpackage Exceptions
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
namespace shgysk8zer0\Core\Exceptions;

/**
 * Exception where the `$code` is compatible with `http_response_code`
 * Useful in cases where throwing an exception should set the response code
 */
final class HTTP extends \Exception
{
	const DEFAULT_MESSAGE = 'Internal Server Error';
	const DEFAULT_CODE    = \shgysk8zer0\Core_API\Abstracts\HTTPStatusCodes::INTERNAL_SERVER_ERROR;

	/**
	 * Create a new exception, with default $code now being 500
	 *
	 * @param string     $message  The exception message
	 * @param int        $code     The exception code
	 * @param \Exception $previous Optional previous exception
	 */
	public function __construct(
		$message             = self::DEFAULT_MESSAGE,
		$code                = self::DEFAULT_CODE,
		\Exception $previous = null
	)
	{
		parent::__construct($message, $code, $previous);
	}

	/**
	 * [__toString description]
	 *
	 * @param void
	 * @return string [description]
	 */
	public function __toString() {
		return $this->getMessage();
	}

	/**
	 * [__invoke description]
	 *
	 * @param  Callable $callback [description]	 *
	 * @return void
	 */
	public function __invoke(Callable $callback = null) {
		if (is_callable($callback)) {
			$callback($this);
		}
		http_response_code($this->getCode());
		exit($this);
	}
}
