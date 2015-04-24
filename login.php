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
	use API\Traits\Passwords;

	const STM_CLASS        = 'PDOStatement';
	const DEFAULT_CON      = 'connect.json';
	const MAGIC_PROPERTY   = '_login_data';
	const RESTRICT_SETTING = true;
	const PASSWORD_ALGO    = PASSWORD_DEFAULT;
	const HASH_COST        = 10;

	/**
	 * Array to store login data
	 * @var array
	 */
	private $_login_data = [
		'user'      => null,
		'password'  => null,
		'role'      => null,
		'logged_in' => false
	];

	/**
	 * Array for crypto cost & salt. Setting salt is a *bad* idea
	 * @var array
	 */
	private $_options = array('cost' => self::HASH_COST);

	/**
	 * Table in database containing user data
	 * @var string
	 */
	private $users_table = 'users';

	/**
	 * Create class instance, connect to database, and set cryptographic options
	 * @param mixed $con     Database credentials object or file
	 * @param array  $options Options array for passsword hashing
	 */
	public function __construct($con = self::DEFAULT_CON, array $options = array())
	{
		parent::connect(
			$con,
			[
				\PDO::ATTR_STATEMENT_CLASS => ['\\' . __NAMESPACE__ . '\\' . self::STM_CLASS]
			]
		);
		$this->_options = array_merge($this->_options, $options);
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

			$source['password'] = $this->_passwordHash($source['password'], $this::PASSWORD_ALGO, $this->_options);

			return $this->prepare(
				"INSERT INTO `{$this->users_table}` (
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
			$results = $this->prepare(
				"SELECT *
				FROM `{$this->users_table}`
				WHERE `user` = :user
				LIMIT 1;"
			)->execute(['user' => $source['user']])
			->getResults(0);

			if ($this->_passwordVerify(
				$source['password'],
				$results->password
			) and $results->role !== 'new') {

				if ($this->_passwordNeedsRehash($results->password, $this::PASSWORD_ALGO, $this->_options)) {
					$results->password = $this->_updatePassword($source['user'], $source['password']);
				}
				$results->logged_in = true;
				$this->{$this::MAGIC_PROPERTY} = array_merge(
					$this->{$this::MAGIC_PROPERTY},
					get_object_vars($results)
				);
				return true;
			}
		}
		return false;
	}

	/**
	 * Undo the login. Destroy it.
	 *
	 * @param void
	 * @return void
	 */
	public function logout()
	{
		$this->{$this::MAGIC_PROPERTY} = array_combine(
			array_keys($this->{$this::MAGIC_PROPERTY}),
			array_pad([], count($this->{$this::MAGIC_PROPERTY}), null)
		);
	}

	/**
	 * Executes query to update password for username and returns password hash
	 *
	 * @param string $username User to update
	 * @param string $password New password to hash
	 * @return string          New password hash
	 */
	protected function _updatePassword($username, $password)
	{
		$password = $this->_passwordHash($password, $this::PASSWORD_ALGO, $this->_options);
		$update = $this->prepare(
			"UPDATE `{$this->users_table}`
			SET `password` = :password
			WHERE `user`   = :username;"
		);
		$update->username = $username;
		$update->pasword  =  $password;
		$update->execute();
		return $password;
	}

	/**
	 * This code will benchmark your server to determine how high of a cost you can
	 * afford. You want to set the highest cost that you can without slowing down
	 * you server too much. 8-10 is a good baseline, and more is good if your servers
	 * are fast enough. The code below aims for ≤ 50 milliseconds stretching time,
	 * which is a good baseline for systems handling interactive logins.
	 *
	 * Based on Example #4 from password_hash documentation
	 *
	 * @param float $timeTarget Target hash times in seconds
	 * @return int              Caclulated cost to use
	 */
	protected function _hashCostBenchmark($timeTarget = 0.05)
	{
		$cost = 8;
		do {
			$start = microtime(true);
			password_hash("test", $this::PASSWORD_ALGO, ["cost" => ++$cost]);
			$end = microtime(true);
		} while (($end - $start) < $timeTarget);
		return $cost;
	}
}
