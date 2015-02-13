<?php
	/**
	 * @author Chris Zuber <shgysk8zer0@gmail.com>
	 * @package shgysk8zer0\Core
	 * @version 1.0.0
	 * @copyright 2014, Chris Zuber
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
	 * Creates and sends a JSON encoded response for XMLHTTPRequests
	 * Optimized to be handled by handleJSON in functions.js
	 *
	 * @example $resp = new json_response();
	 * $resp
	 * 		->notify(...)
	 * 		->html(...)
	 * 		->append(...)
	 * 		->prepend(...)
	 * 		->before(...)
	 * 		->after(...)
	 * 		->attributes(...)
	 * 		->remove(...)
	 * 		->send();
	 */
	class JSON_Response implements API\Interfaces\Magic_Methods, API\Interfaces\AJAX_DOM
	{
		use API\Traits\Singleton;
		use API\Traits\Magic_Methods;
		use API\Traits\AJAX_DOM;

		const CONTENT_TYPE = 'application/json';
		const MAGIC_PROPERTY = 'response';

		/**
		 * Initialize the class, optionally with data to start with
		 *
		 * @param array $arr Optional initial data
		 */
		public function __construct(array $arr = array())
		{
			$this->response = $arr;
		}

		/**
		 * Chained magic getter and setter (and isset via has)
		 *
		 * @param string $name
		 * @param array $arguments
		 * @return mixed
		 * @example "$resp->[getName|setName|hasName]($value)"
		 * @method get*
		 * @method set*
		 * @method has*
		 */
		final public function __call($name, array $arguments = array())
		{
			$name = strtolower($name);
			$act = substr($name, 0, 3);
			$key = substr($name, 3);
			switch($act) {
				case 'get':
					if (array_key_exists($key, $this->response)) {
						return $this->response[$key];
					} else {
						return null;
					}
				case 'set':
					$this->response[$key] = current($arguments);
					return $this;
				case 'has':
					return array_key_exists($key, $this->response);
			}
		}

		/**
		 * Sends everything with content-type of application/json,
		 * Exits with json_encode($this->response)
		 * An optional $key argument can be used to only
		 * send a subset of $this->response
		 *
		 * @param string $key
		 * @return void
		 * @example $resp->send() or $resp->send('notify')
		 */
		public function send($key = null)
		{
			if (count($this->response) and !headers_sent()) {
				header('Content-Type: ' . $this::CONTENT_TYPE);
				(is_string($key))
					? exit(json_encode([$key => $this->response[$key]]))
					: exit(json_encode($this->response));
			} else {
				http_response_code(403);
				exit();
			}
		}
	}
