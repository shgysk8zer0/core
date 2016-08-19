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
 * Send cURL requests with the body as a JSON encoded string
 */
class JSONcURL extends API\Abstracts\cURL_Request
implements API\Interfaces\cURL, API\Interfaces\toString, API\Interfaces\Magic_Methods
{
	use API\Traits\Magic_Methods;
	const CONTENT_TYPE = 'application/json';
	const MAGIC_PROPERTY = '_data';

	/**
	 * Array for storing headers, such as COntent-Type
	 *
	 * @var array
	 */
	protected $headers = array(
		'Content-Type' => self::CONTENT_TYPE
	);

	/**
	 * Array for storing request data, to later be JSON encoded when sending
	 *
	 * @var array
	 */
	private $_data = array();

	/**
	 * JSON encodes request data
	 *
	 * @param void
	 * @return string JSON encoded request data
	 */
	public function __toString()
	{
		return json_encode($this->{self::MAGIC_PROPERTY});
	}

	/**
	 * Uses curl_exec to send request as JSON string with correct content-type header
	 *
	 * @return \stdClass  Parsed response from server, including headers
	 */
	public function send()
	{
		return parent::send("{$this}");
	}
}
