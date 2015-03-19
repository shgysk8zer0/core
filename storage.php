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
 * @deprecated
 */

namespace shgysk8zer0\Core;

use \shgysk8zer0\Core_API as API;

/**
 * Consists almost entirely of magic methods.
 * Functionality is similar to globals, except new entries may be made
 * and the class also has save/load methods for saving to or loading from $_SESSION
 * Uses a private array for storage, and magic methods for getters and setters
 *
 * I just prefer using $session->key over $_SESSION[key]
 * It also provides some chaining, so $session->setName(value)->setOtherName(value2)->getExisting() can be done
 */
final class Storage implements API\Interfaces\Magic_Methods
{
	use API\Traits\Magic_Methods;
	use API\Traits\Magic_Call;
	use API\Traits\Singleton;

	private $data = [];

	const MAGIC_PROPERTY = 'data';
	const SESSION_KEY = 'storage';

	/**
	 * Returns an array of all array keys for $this->data
	 *
	 * @param void
	 * @return array
	 */
	public function keys()
	{
		return array_keys($this->{self::MAGIC_PROPERTY});
	}

	/**
	 * Saves all $data to $_SESSION
	 *
	 * @param void
	 * @return void
	 */
	public function save()
	{
		$_SESSION[self::SESSION_KEY] = serialize($this->{self::MAGIC_PROPERTY});
	}

	/**
	 * Loads existing $data array from $_SESSION
	 *
	 * @param void
	 * @return void
	 */
	public function restore()
	{
		if (array_key_exists(self::SESSION_KEY, $_SESSION)) {
			$this->{self::MAGIC_PROPERTY} = unserialize($_SESSION[self::SESSION_KEY]);
		}
	}

	/**
	 * Destroys/clears/deletes
	 * This message will self destruct
	 *
	 * @param void
	 * @return void
	 */
	public function clear()
	{
		unset($this->{self::MAGIC_PROPERTY});
		unset($this);
	}
}
