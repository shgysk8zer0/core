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
 * Create an XML document and send it in a cURL request
 */
class XML_cURL extends API\Abstracts\XML_Document
{
	use API\Traits\cURL;

	const PREFIX       = 'CURLOPT_';
	const CONTENT_TYPE = 'application/xml';

	/**
	 * Default array of cURL options to set
	 * @var array
	 */
	private $default_opts = [
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_HEADER         => true,
		CURLOPT_SSL_VERIFYPEER => true,
		CURLOPT_POST           => true,
		CURLOPT_CONNECTTIMEOUT => 30,
		CURLOPT_TIMEOUT        => 30
	];

	/**
	 * Default array of headers to set in CURLOPT_HTTPHEADER
	 * @var array
	 */
	private $headers = [
		'Content-Type' => self::CONTENT_TYPE
	];

	/**
	 * Sets the HTTP Accept header to these values (supported response formats)
	 * @var array
	 */
	private $accept = [
		'application/json',
		'application/xml',
		'application/x-www-form-urlencoded',
		'application/vnd.php.serialized',
		'text/plain'
	];

	public $parse_response = true;

	/**
	 * Create a new XML cURL API call instance
	 *
	 * @param string $url       URL to send the request to
	 * @param array  $curl_opts Array of additional cURL options to set
	 * @param array  $headers   Array of headers to set in addition to defaults
	 */
	public function __construct(
		$url = null,
		array $curl_opts = array(),
		array $headers = array()
	)
	{
		parent::__construct(
			$this::VERSION,
			$this::ENCODING,
			$this::ROOT_EL,
			$this::XMLNS
		);
		$this->curlInit($url);
		$this->headers['Accept'] = join(',', $this->accept);
		$headers = array_merge($this->headers, $headers);

		$this->curlSetOptArray($this->default_opts);
		$this->curlSetOpt(CURLOPT_HTTPHEADER, array_map(
			function($key, $value)
			{
				return "{$key}: {$value}";
			},
			array_keys($headers),
			array_values($headers)
		));

		$this->curlSetOptArray($curl_opts);
	}

	public function send()
	{
		$this->curlSetOpt(CURLOPT_POSTFIELDS, "{$this}");

		$response = new \stdClass;

		try {
			$response->body = $this->curlExec();
			$header_size = $this->curlGetinfo(CURLINFO_HEADER_SIZE);
			$response->headers = http_parse_headers(
				substr($response->body, 0, $header_size)
			);
			$body = substr($response->body, $header_size);

			if (
				$this->parse_response
				and array_key_exists('Content-Type', $response->headers)
			) {
				switch ($response->headers['Content-Type']) {
					case 'application/json':
						$response->body = json_decode($body);
						break;

					case 'application/xml':
						$response->body = simplexml_load_string($body);
						break;

					case 'application/x-www-form-urlencoded':
						parse_str($body, $response->body);
						break;

					case 'application/vnd.php.serialized':
						$response->body = unserialize($body);
						break;

					case 'text/plain':
						$response->body = $body;
						break;

					default:
						$response->body = $body;
						break;
				}
				unset($body);
			} else {
				$response->body = $body;
			}
			$response->errno = $this->curlErrno();
			$response->error = $this->curlError();
			return $response;
		} catch(\Exception $e) {
			exit($e);
		}
	}
}
