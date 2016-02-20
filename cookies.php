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
 * Quick and easy way of setting/getting cookies
 *
 * @example
 * $cookies = new \shgysk8zer0\Core\cookies();
 * $cookies->cookie_name = 'Value';
 * $cookie->existing_cookie //Returns value of $_COOKIES['existing-cookie']
 */
final class Cookies implements API\Interfaces\Magic_Methods, \Iterator
{
	use API\Traits\Magic\Call;
	use API\Traits\Singleton;
	use API\Traits\GetInstance;

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
	protected $domain = 'localhost';

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
	 * Initializes cookies class, setting all properties (similar to arguments)
	 *
	 * @param string  $domain   Whether or not to limit cookie to https connections
	 * @param string  $path     example.com/path would be /path
	 * @param mixed   $expires  Takes a variety of date formats, including timestamps
	 * @param bool    $secure   Setting to true prevents access by JavaScript, etc
	 * @param bool    $httponly Setting to true prevents access by JavaScript, etc
	 * @example $cookies = new cookies('Tomorrow', '/path', 'example.com', true, true);
	 */
	public function __construct(
		$domain   = 'localhost',
		$path     = '/',
		$expires  = 0,
		$secure   = false,
		$httponly = false
	)
	{
		$this->expires = (int) is_numeric($expires)
			? (int)$expires
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
	 * @param string $key   Name of cookie to set
	 * @param string $value  Value to set it to
	 * @example $cookies->test = 'Works'
	 */
	public function __set($key, $value)
	{
		$this->_convertKey($key);
		$_COOKIE[$key] = $value;
		setcookie(
			$key,
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
	 * @param string $key   Name of cookie to get
	 * @return mixed Value of requested cookie
	 * @example $cookies->test // returns 'Works'
	 */
	public function __get($key)
	{
		$this->_convertKey($key);
		return isset($this->$key) ? $_COOKIE[$key] : null;
	}

	/**
	 * Checks if $_COOKIE[$key] exists
	 *
	 * @param string $key  Name of cookie to test if exists
	 * @return bool
	 * @example isset($cookies->$key) (true)
	 */
	public function __isset($key)
	{
		$this->_convertKey($key);
		return array_key_exists($key, $_COOKIE);
	}

	/**
	 * Completely destroys a cookie on server and client
	 *
	 * @param string $key  Name of cookie to remove
	 * @return void
	 * @example unset($cookies->$key)
	 */
	public function __unset($key)
	{
		$this->_convertKey($key);
		if (isset($this->$key)) {
			unset($_COOKIE[$key]);
			setcookie(
				$key,
				null,
				-1,
				$this->path,
				$this->domain,
				$this->secure,
				$this->httponly
			);
		}
	}

	/**
	 * Gets the value @ $_iterator_position
	 *
	 * @param void
	 * @return mixed Whatever the current value is
	 */
	public function current()
	{
		return $_COOKIE[$this->key()];
	}

	/**
	 * Returns the original key (not $_iterator_position) at the current position
	 *
	 * @param void
	 * @return mixed  Probably a string, but could be an integer.
	 */
	public function key()
	{
		return key($_COOKIE);
	}

	/**
	 * Increment $_iterator_position
	 *
	 * @param void
	 * @return void
	 */
	public function next()
	{
		next($_COOKIE);
	}

	/**
	 * Reset $_iterator_position to 0
	 *
	 * @param void
	 * @return void
	 */
	public function rewind()
	{
		reset($_COOKIE);
	}

	/**
	 * Checks if data is set for current $_iterator_position
	 *
	 * @param void
	 * @return bool Whether or not there is data set at current position
	 */
	public function valid()
	{
		return $this->key() !== null;
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

	/**
	 * Provides a single & consistent method to convert keys in magic methods
	 *
	 * @param string $key Reference to the key given.
	 * @return self
	 * @example $this->_convertKey($key);
	 */
	private function _convertKey(&$key)
	{
		$key = str_replace('_', '-', $key);
		return $this;
	}
}
