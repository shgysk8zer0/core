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
 * Class to handle login or create new users from form submissions or $_SESSION
 * Can check login role as well (new, user, admin, etc)
 */
use \shgysk8zer0\Core_API as API;

/**
 * Checks login status, creates or logs in with an array, and can logout as well.
 */
class Login extends API\Abstracts\PDO_Connect
implements API\Interfaces\PDO, API\Interfaces\Magic_Methods
{
	use API\Traits\Singleton;
	use API\Traits\Magic_Methods;
	use API\Traits\Magic\Call;
	use API\Traits\PDO;

	const STM_CLASS = 'PDOStatement';
	const DEFAULT_CON = 'connect.json';
	const MAGIC_PROPERTY = 'data';
	const USER_TABLE = '`users`';
	const RESTRICT_SETTING = true;

	/**
	 * Array to store login data
	 * @var array
	 */
	private $data = [
		'user' => null,
		'password' => null,
		'role' => null,
		'logged_in' => false
	];

	public function __construct($con = self::DEFAULT_CON)
	{
		parent::connect(
			$con,
			[
				self::ATTR_STATEMENT_CLASS => ['\\' . __NAMESPACE__ . '\\' . self::STM_CLASS]
			]
		);
	}

	/**
	 * Creates new user using an array passed as source. Usually $_POST or $_SESSION
	 *
	 * @param array $source
	 * @return bool
	 * @example $login->createFrom($_POST|$_GET|$_REQUEST|array())
	 */
	public function createFrom(array $source = array())
	{
		if (
			array_key_exists('user', $source)
			and array_key_exists('password', $source)
		) {
			$keys = array_map(function($key) {
				return preg_replace('/\W/', null, $key);
			}, array_keys($source));

			$source = array_combine($keys, array_values($source));

			$source['password'] = password_hash(
				$source['password'],
				PASSWORD_DEFAULT
			);

			return $this->prepare(
				"INSERT INTO " . self::USER_TABLE . "(
					`" . join('`, `', $keys) . "`
				) VALUES ("
					. join(', ', array_map(function($key) {
						return ":{$key}";
					}, $keys)) . "
				);"
			)->execute($source);
		} else {
			return false;
		}
	}

	/**
	 * Intended to find login info from $_COOKIE, $_SESSION, or $_POST
	 *
	 * @param array $source
	 * @return void
	 * @example $login->loginWith($_POST|$_GET|$_REQUEST|$_SESSION|array())
	 */
	public function loginWith(array $source = array())
	{
		if (
			array_key_exists('user', $source)
			and array_key_exists('password', $source)
		) {
			array_walk($source, 'trim');
			$results = $this->prepare(
				"SELECT *
				FROM " . self::USER_TABLE . "
				WHERE `user` = :user
				LIMIT 1;"
			)->execute(['user' => $source['user']])
			->getResults(0);

			if (password_verify(
				$source['password'],
				$results->password
			) and $results->role !== 'new') {
				$results->logged_in = true;
				$this->data = array_merge(
					$this->data,
					get_object_vars($results)
				);
				return true;
			}
		}
		return false;
	}

	/**
	 * Undo the login. Destroy it. Removes session and cookie.
	 *
	 * @param void
	 * @return void
	 */
	public function logout()
	{
		$this->data = array_combine(
			array_keys($this->data),
			array_pad([], count($this->data), null)
		);
	}
}
