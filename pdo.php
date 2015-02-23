<?php
/**
 * @author Chris Zuber <shgysk8zer0@gmail.com>
 * @package shgysk8zer0\Core
 * @version 01.0.0
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
 * Provides easyier to use and often chainable methods for PDO
 */
class PDO
extends API\Abstracts\PDO_Connect
implements API\Interfaces\PDO, API\Interfaces\File_IO, Interfaces\Legacy_PDO
{
	use API\Traits\PDO;
	use Traits\PDO;
	use Traits\Legacy_PDO;
	use API\Traits\Singleton;
	use API\Traits\PDO_Backups;

	const STM_CLASS = 'PDOStatement';
	const DEFAULT_CON = 'connect.json';

	/**
	 * Creates a new Databse connection/PDO instance
	 *
	 * @param mixed $con Credentials as filename, object, array.
	 */
	public function __construct($con = null)
	{
		parent::connect(
			$con,
			[
				self::ATTR_STATEMENT_CLASS => ['\\' . __NAMESPACE__ . '\\' . self::STM_CLASS]
			]
		);
	}

	/**
	 * Allow read-only access to protected properties
	 *
	 * @param  string $prop Name of property
	 * @return mixed        Value of property
	 */
	final public function __get($prop)
	{
		if (isset($this->{$prop})) {
			return $this->{$prop};
		} else {
			return null;
		}
	}
}
