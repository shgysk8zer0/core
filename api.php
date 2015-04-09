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

use \shgysk8zer0\Core_API as Core_API;

/**
 * Generic API class for parsing requests & responses
 */
class API implements API\Interfaces\String, API\Interfaces\Magic_Methods
{
	use Core_API\Traits\Magic_Methods;
	use Core_API\Traits\Magic\Call;

	const MAGIC_PROPERTY = 'response';

	/**
	 * Data to send in response body
	 * @var array
	 */
	protected $response = [];

	/**
	 * Request headers
	 * @var array
	 */
	public $headers     = [];

	/**
	 * Parsed request body
	 * @var mixed
	 */
	public $request     = null;

	/**
	 * Prefered response format, if given in the Accept header
	 * @var string
	 */
	public static $response_format = 'application/json';

	/**
	 * Creates a new API instance, parses request headers & body
	 *
	 * @param void
	 */
	public function __construct()
	{
		$this->getHeaders();
		$this->getRequest();
	}

	/**
	 * Sanitizes all request headers (converts to lower-case)
	 *
	 * @param void
	 * @return void
	 */
	final public function getHeaders()
	{
		foreach(getallheaders() as $k => $v) {
			$this->headers[strtolower($k)] = $v;
		}

		if (! array_key_exists('accept', $this->headers)) {
			$this->headers['accept'] = '*/*';
		}
	}

	/**
	 * Parses request body if it is of a supported Content-Type
	 *
	 * @param void
	 * @return void
	 */
	final public function getRequest()
	{
		$req = file_get_contents('php://input');

		// Verify Content-Length of request & parse according to Content-Type
		try {
			if (! array_key_exists('content-length', $this->headers)) {
				throw new \Exception(
					'Content-Length required but not given',
					411
				);
			} elseif (! array_key_exists('content-type', $this->headers)) {
				throw new \Exception(
					'Content-Type required but not given',
					406
				);
			} elseif (strlen($req) === (int)$this->headers['content-length']) {
				// Sanitize Content-Type, converting to lowercase and removing
				// extra data, such as charset
				switch (
					strtolower(
						current(explode(';', $this->headers['content-type']))
					)
				) {
					case 'application/xml':
						$this->request = simplexml_load_string($req);
						break;

					case 'application/json':
						$this->request = json_decode($req);
						break;

					case 'application/x-www-form-urlencoded':
						parse_str($req, $this->request);
						break;

					case 'application/vnd.php.serialized':
						$this->request = unserialize($req);
						break;

					case 'text/plain':
						$this->request =$req;
						break;

					default:
						throw new \Exception(
							sprintf(
								'%s is not a supported Content-Type',
								$this->headers['content-type']
							),
							415
						);
				}
			} else {
				// Content-Length is set & valid. Content-Length is set but
				// not supported.
				throw new \Exception(
					sprintf(
						'Invalid Content-Length: Expected %d but got %d',
						(int)$this->headers['content-length'],
						strlen($req)
					),
					411
				);
			}
		} catch(\Exception $e) {
			http_response_code($e->getCode());
			exit($e->getMessage());
		}
	}

	/**
	 * Create request as a string according to the HTTP Accept header
	 * Sets the correct Content-Type header
	 *
	 * @param void
	 * @return string Request body converted to a string, varying on support
	 */
	final public function __toString()
	{
		try {
			// $accepts array will contain lowercase entries from Accept header
			// with any extra data such as charset removed
			$accepts = array_map(
				function($type)
				{
					return strtolower(trim(current(explode(';', $type))));
				},
				explode(',', $this->headers['accept'])
			);

			// Check if HTTP Accept header is all (*/*) or if $response_format
			// is in array of Accept headers ($accepts)
			if (
				$this->headers['accept'] === '*/*'
				or ! in_array(
					static::$response_format,
					$accepts
				)
			) {
				static::$response_format = current($accepts);
			}

			unset($accepts);

			// Convert $response and set Content-Type header according to format
			switch (strtolower(static::$response_format)) {
				case 'application/json':
					header('Content-Type: application/json');
					return json_encode($this->{self::MAGIC_PROPERTY});
					break;

				case 'application/xml':
					header('Content-Type: application/xml');
					return (string) new XML_Doc(
						'Response',
						$this->{self::MAGIC_PROPERTY}
					);
					break;

				case 'application/x-www-form-urlencoded':
					header('Content-Type: application/x-www-form-urlencoded');
					return http_build_query($this->{self::MAGIC_PROPERTY});
					break;

				case 'application/vnd.php.serialized':
					header('Content-Type: application/vnd.php.serialized');
					return serialize($this->{self::MAGIC_PROPERTY});
					break;

				case 'text/plain':
					header('Content-Type: text/plain');
					return print_r($this->{self::MAGIC_PROPERTY});
					break;

				default:
					throw new \Exception(
						sprintf(
							'Unable to respond with Content-Type of %s',
							$this->response_format
						),
						415
					);
					break;
			}
		} catch (\Exception $e) {
			http_response_code($e->getCode());
			exit($e->getMessage());
		}
	}

	/**
	 * Exits, sending the request
	 *
	 * @return void
	 */
	final public function send()
	{
		// Clear any content from buffer, such as errors, leaving only response
		ob_get_clean();

		// exit will convert to string, so __toString will be used
		exit($this);
	}
}
