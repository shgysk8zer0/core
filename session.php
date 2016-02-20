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
 * Since this class is using $_SESSION for all data, there are few variables
 * There are several methods to make better use of $_SESSION, and it adds the
 * ability to chain. As $_SESSION is used for all storage, there is no pro or
 * con to using __construct vs ::load()
*/
class Session implements API\Interfaces\Magic_Methods
{
	use API\Traits\Singleton;
	use API\Traits\Magic\Call;
	use API\Traits\GetInstance;

	/**
	 * Creates new instance of session. $name is optional, and sets session_name
	 * if session has not been started
	 *
	 * @param string $site optional name for session
	 * @return void
	 */
	public function __construct($name = null)
	{
		//Do not create new session of one has already been created
		if (session_status() !== PHP_SESSION_ACTIVE) {
			//Avoid trying to figure out cookie paramaters for CLI
			if (PHP_SAPI != 'cli') {

				if (! is_string($name)) {
					$path = explode(DIRECTORY_SEPARATOR, BASE);
					$name = end($path);
					unset($path);
				}

				$name = preg_replace('/[\W]/', null, strtolower($name));
				session_name($name);

				if (! array_key_exists($name, $_COOKIE)) {
					session_set_cookie_params(
						0,
						parse_url(URL, PHP_URL_PATH),
						parse_url(URL, PHP_URL_HOST),
						https(),
						true
					);
				}
			}
			session_start();
		}
	}

	/**
	 * The getter method for the class.
	 *
	 * @param string $key  Name of property to retrieve
	 * @return mixed       Its value
	 * @example "$session->key" Returns $value
	 */
	public function __get($key)
	{
		if (isset($this->$key)) {
			return $_SESSION[$this->getKey($key)];
		}
		return null;
	}

	/**
	 * Setter method for the class.
	 *
	 * @param string $key   Name of property to set
	 * @param mixed $value  Value to set it to
	 * @return void
	 * @example "$session->key = $value"
	 */
	public function __set($key, $value)
	{
		$_SESSION[$this->getKey($key)] = $value;
	}

	/**
	 * @param string $key  Name of property to check
	 * @return bool        Whether or not it is set
	 * @example "isset({$session->key})"
	 */
	public function __isset($key)
	{
		return array_key_exists($this->getKey($key), $_SESSION);
	}

	/**
	 * Removes an index from the array.
	 *
	 * @param string $key  Name of property to unset/remove
	 * @return void
	 * @example "unset($session->key)"
	 */
	public function __unset($key)
	{
		unset($_SESSION[$this->getKey($key)]);
	}

	/**
	* Destroys $_SESSION and attempts to destroy the associated cookie
	*
	* @param void
	* @return void
	*/
	public function destroy()
	{
		$name = session_name();
		session_destroy();
		unset($_SESSION);

		if (array_key_exists($name, $_COOKIE)) {
			unset($_COOKIE[$name]);
			setcookie($name, null, -1);
		}
	}

	/**
	 * Clear $_SESSION. All data in $_SESSION is unset
	 *
	 * @param void
	 * @return self
	 * @example $session->restart()
	 */
	public function restart() {
		session_unset();
		return $this;
	}

	/**
	 * Converts array key for $_SESSION into something consistent
	 *
	 * @param string $key The original value
	 * @return string     The converted value
	 */
	private function getKey($key)
	{
		return strtolower(str_replace('_', '-', $key));
	}
}
