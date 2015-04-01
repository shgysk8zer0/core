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
 * Class for responding to AJAX requests with JSON encoded versions of common DOM
 * methods. Requires compatible handler in JavaScript
 *
 * @example $resp = new json_response();
 * echo $resp
 * 		->notify(...)
 * 		->html(...)
 * 		->append(...)
 * 		->prepend(...)
 * 		->before(...)
 * 		->after(...)
 * 		->attributes(...)
 * 		->remove(...);
 */
use \shgysk8zer0\Core_API as API;

/**
 * Creates and sends a JSON encoded response for XMLHTTPRequests
 * Optimized to be handled by handleJSON in functions.js
 */
final class JSON_Response implements API\Interfaces\Magic_Methods, API\Interfaces\AJAX_DOM
{
	use API\Traits\Singleton;
	use API\Traits\Magic_Methods;
	use API\Traits\Magic_Call;
	use API\Traits\AJAX_DOM;

	const CONTENT_TYPE = 'application/json';
	const MAGIC_PROPERTY = 'response';

	/**
	 * Initialize the class, optionally with data to start with
	 *
	 * @param array $arr Optional initial data
	 */
	public function __construct($arr = null)
	{
		if (is_array($arr)) {
			$this->{self::MAGIC_PROPERTY} = $arr;
		}
	}

	/**
	 * Returns the current data when class is converted to string, e.g. echo.
	 *
	 * @param void
	 * @return string
	 * @example echo $resp
	 * @example exit($resp)
	 * @example $var = "$resp"
	 */
	public function __toString()
	{
		header('Content-Type: ' . self::CONTENT_TYPE);
		$json = $this->{self::MAGIC_PROPERTY};
		$this->{self::MAGIC_PROPERTY} = [];
		return json_encode($json);
	}

	/**
	 * Sends everything with content-type of application/json,
	 * Exits with json_encode($this->response)
	 *
	 * @param void
	 * @return void
	 * @example $resp->send()
	 * @deprecated Use `exit($resp)` instead.
	 */
	public function send()
	{
		ob_get_clean();
		if (! empty($this->{self::MAGIC_PROPERTY}) and ! headers_sent()) {
			exit($this);
		} else {
			http_response_code(403);
			exit();
		}
	}
}
