<?php
/**
 * @author Chris Zuber
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

final class Analytics extends PDO
{
	/**
	 * User-Agent string
	 * @var string
	 */
	private $UA = '';

	/**
	 * Remote IP Address
	 * @var string
	 */
	private $IP = '';

	/**
	 * Requested URI (not including origin)
	 * @var string
	 */
	private $URL = '';

	/**
	 * HTTP Accept header
	 * @var string
	 */
	private $lang = '';

	/**
	 * Request time as Unix timestamp
	 * @var integer
	 */
	private $date = 0;

	/**
	 * HTTP status code
	 * @var integer
	 */
	private $HTTP_status = 200;

	/**
	 * [__construct description]
	 */
	public function __construct($con = self::DEFAULT_CON)
	{
		parent::__construct($con);
		$this->_getInfo();
	}

	private function _getInfo()
	{
		date_default_timezone_set('America/Los_Angeles');
		$date = new \DateTime;
		$this->UA = $_SERVER['HTTP_USER_AGENT'];
		$this->IP = $_SERVER['REMOTE_ADDR'];
		$this->URL = $_SERVER['REQUEST_URI'];
		$this->lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
		$this->date = $date->format($date::W3C);
		$this->HTTP_status = http_response_code();
	}

	private function _getStm()
	{
		return $this->prepare('INSERT INTO ');
	}
}
