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
 * Quick and easy way of setting/getting cookies
 *
 * @example
 * $cookies = new \shgysk8zer0\Core\cookies();
 * $cookies->cookie_name = 'Value';
 * $cookie->existing_cookie //Returns value of $_COOKIES['existing-cookie']
 */
class cookies implements \shgysk8zer0\Core_API\Interfaces\Magic_Methods
{
	use \shgysk8zer0\Core_API\Traits\Magic\Call;

	/**
	 * Timestamp of when the cookie expires
	 * @var int
	 */
	public $expires = 0;

	/**
	 * Path relative to DOCUMENT_ROOT/SERVER_NAME where the cookie is used
	 * @var string
	 */
	public $path = '/';

	/**
	 * Name of server/domain the cookie is valid at
	 * @var string
	 */
	public $domain = 'localhost';

	/**
	 * Use cookie only over HTTPS?
	 * @var bool
	 */
	public $secure = false;

	/**
	 * Only send over HTTP requests (blocks access to JavaScript)
	 * @var bool
	 */
	public $httponly = false;

	/**
	 * Static instance of class to prevent multiple loadings/instances
	 * @var \shgysk8zer0\Core\Cookies
	 */
	private static $instance = null;

	/**
	 * Static method for creating class
	 *
	 * See __construct documentation
	 */
	public static function load(
		$expires = 0,
		$path = null,
		$domain = null,
		$secure = null,
		$httponly = null
	)
	{
		if (is_null(self::$instance)) {
			self::$instance = new self(
				$expires = 0,
				$path = null,
				$domain = null,
				$secure = null,
				$httponly = null
			);
		}

		return self::$instance;
	}

	/**
	 * Initializes cookies class, setting all properties (similar to arguments)
	 *
	 * @param mixed   $expires  Takes a variety of date formats, including timestamps
	 * @param string  $path     example.com/path would be /path
	 * @param string  $domain   Whether or not to limit cookie to https connections
	 * @param bool    $secure   Setting to true prevents access by JavaScript, etc
	 * @param bool    $httponly Setting to true prevents access by JavaScript, etc
	 * @example $cookies = new cookies('Tomorrow', '/path', 'example.com', true, true);
	 */
	public function __construct(
		$expires = 0,
		$path = null,
		$domain = null,
		$secure = null,
		$httponly = null
	)
	{
		$this->expires = (int) is_numeric($expires)
			? $expires
			: strtotime($expires);

		$this->path = (is_string($path))
			? $path
			: parse_url(URL, PHP_URL_PATH);

		if (is_string($domain)) {
			$this->domain = $domain;
		} elseif (array_key_exists('HTTP_HOST', $_SERVER)) {
			$this->domain = $_SERVER['HTTP_HOST'];
		} elseif (array_key_exists('SERVER_NAME', $_SERVER)) {
			$this->domain = $_SERVER['SERVER_NAME'];
		} else {
			$this->domain = parse_url(URL, PHP_URL_HOST);
		}

		$this->secure = (is_bool($secure)) ? $secure : false;
		$this->httponly = (is_bool($httponly)) ? $httponly : false;
	}

	/**
	 * Magic setter for the class.
	 * Sets a cookie using only $name and $value. All
	 * other paramaters set in __construct
	 *
	 * @param string $name   Name of cookie to set
	 * @param string $value  Value to set it to
	 * @example $cookies->test = 'Works'
	 */
	public function __set($name, $value)
	{
		setcookie(
			str_replace('_', '-', $name),
			(string)$value,
			$this->expires,
			$this->path,
			$this->domain,
			$this->secure,
			$this->httponly
		);
	}

	/**
	 * Magic getter for the class
	 *
	 * Returns the requested cookie's value or false if not set
	 *
	 * @param string $name   Name of cookie to get
	 * @return mixed Value of requested cookie
	 * @example $cookies->test // returns 'Works'
	 */
	public function __get($name)
	{
		$name = str_replace('_', '-', $name);
		return isset($this->$name) ? $_COOKIE[$name] : null;
	}

	/**
	 * Checks if $_COOKIE[$name] exists
	 *
	 * @param string $name  Name of cookie to test if exists
	 * @return bool
	 * @example isset($cookies->test) (true)
	 */
	public function __isset($name)
	{
		return array_key_exists(str_replace('_', '-', $name), $_COOKIE);
	}

	/**
	 * Completely destroys a cookie on server and client
	 *
	 * @param string $name  Name of cookie to remove
	 * @return void
	 * @example unset($cookies->$name)
	 */
	public function __unset($name)
	{
		$name = str_replace('_', '-', $name);
		if (isset($this->$name)) {
			unset($_COOKIE[$name]);
			setcookie($name, null, -1, $this->path, $this->domain, $this->secure, $this->httponly);
		}
	}

	/**
	 * Lists all cookies by name
	 *
	 * @param void
	 * @return array
	 * @example $cookies->keys() (['test', ...])
	 * @deprecated
	 */
	public function keys()
	{
		return array_keys($_COOKIE);
	}

}
