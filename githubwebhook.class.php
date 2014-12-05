<?php
	/**
	 * Handle Webhooks from GitHub with ease!
	 *
	 * @author Chris Zuber <shgysk8zer0@gmail.com>
	 * @copyright 2014, Chris Zuber
	 *
	 * @var array     $headers   [The headers sent]
	 * @var string    $payload   [The body]
	 * @var string    $event     [Event declared in X-Github-Event header]
	 * @var \stdClass $parsed    [$payload parsed as an Object]
	 * @var \stdClass $config    [Configuration data parsed as an Object]
	 *
	 * @link https://developer.github.com/webhooks/
	 *
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


	namespace core;
	class GitHubWebhook {
		public $headers = [], $payload = null, $event = null, $parsed = null;
		protected $config = null;

		/**
		 * Construct the class & set its variables
		 *
		 * @param mixed $config [Configuration data, possibly an .ini or .json]
		 */

		public function __construct($config = null) {
			$this->headers = getallheaders();
			if(array_key_exists('X-GitHub-Event', $this->headers)) {
				$this->event = $this->headers['X-GitHub-Event'];
			}

			if(isset($config)) {
				$this->parseConfig($config);
			}

			$this->parsePayload();

		}

		/**
		 * Validate Content-Length, User-Agent, and X-Hub-Signature
		 *
		 * @link https://github.com/github/github-services/blob/f3bb3dd780feb6318c42b2db064ed6d481b70a1f/lib/service/http_helper.rb#L77
		 *
		 * @param  string $secret [Secret key set when creating the Webhook]
		 * @return bool
		 */

		public function validate($secret = null) {
			if(
				is_null($secret)
				and is_object($this->config)
				and @is_string($this->config->secret)
			) {
				$secret = $this->config->secret;
			}

			if(
				array_key_exists('Content-Length', $this->headers)
				and array_key_exists('User-Agent', $this->headers)
				and preg_match('/^GitHub-Hookshot/', $this->headers['User-Agent'])
				and array_key_exists('X-Hub-Signature', $this->headers)
			) {
				if(is_string($secret)) {
					list($algo, $hash) = explode('=', $this->headers['X-Hub-Signature'], 2);

					return $hash === hash_hmac(
						$algo,
						$this->payload,
						$secret
					);
				}

				else {
					return true;
				}
			}

			else {
				return false;
			}
		}

		/**
		 * Parse $config, whether it be an Object or a file
		 *
		 * @param mixed $config [Configuration data, possibly an .ini or .json]
		 * @return void
		 */

		private function parseConfig($config) {
			if(is_string($config) and @file_exists($config) and @is_readable($config)) {
				switch(strtolower(pathinfo($config, PATHINFO_EXTENSION))) {
					case 'json': {
						$this->config = json_decode(file_get_contents($config));
					} break;
					case 'ini': {
						$this->config = (object)parse_ini_file($config);
					} break;
				}
			}

			elseif(is_string($config)) {
				$this->config = new \stdClass();
				$this->config->secret = $config;
			}

			elseif(is_array($config)) {
				$this->config = (object)$config;
			}

			elseif(is_object($config)) {
				$this->config = $config;
			}
		}

		/**
		 * Parses data from $payload into an object
		 * @todo Make work with different content-type (form-data)
		 *
		 * @param void
		 * @return void
		 */

		private function parsePayload() {
			if(array_key_exists('content-type', $this->headers)) {
				switch(strtolower($this->headers['content-type'])) {
					case 'application/json': {
						$this->payload = file_get_contents('php://input');
						if(strlen($this->payload) === (int)$this->headers['Content-Length']) {
							$this->parsed = json_decode($this->payload);
						}
					} break;
				}
			}
		}
	}
?>
