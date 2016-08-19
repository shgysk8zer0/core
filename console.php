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

final class Console extends API\Abstracts\Console implements API\Interfaces\toString, API\Interfaces\Console
{
	use API\Traits\Magic_Methods;
	use API\Traits\GetInstance;
	use API\Traits\Console;
	use API\Traits\ConsoleHandlers;

	const MAGIC_PROPERTY = '_settings';

	/**
	 * Set and send custom header only when class is destroyed
	 *
	 * @param void
	 * @return void
	 */
	public function __destruct()
	{
		$this->sendLogHeader();
	}

	/**
	 * Get log data as a UTF-8 and JSON encoded string
	 *
	 * @param void
	 * @return string Encoded contents of $this->_json
	 */
	public function __toString()
	{
		return $this->_encodeLogHeader();
	}
}
