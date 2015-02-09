<?php

	namespace shgysk8zer0\Core;
	/**
	 * Wrapper for standard PDO class. Allows
	 * standard MySQL to be used, while giving benefits
	 * of chained prepare->bind->execute...
	 *
	 * @author Chris Zuber <shgysk8zer0@gmail.com>
	 * @package shgysk8zer0\Core
	 * @version 0.9.0
	 * @copyright 2014, Chris Zuber
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
	 *
	 * @todo Remove Prepared methods and move to another class.
	 * @todo Move methods into traits.
	 * @todo Remove bloat methods.
	*/
	use \shgysk8zer0\Core\Resources\PDO_Resources as PDO_Resources;
	class PDO extends PDO_Resources implements Interfaces\PDO
	{
		use \shgysk8zer0\Core\Traits\Depreciated\PDO;
		/**
		* Gets database connection info from config file
		* The default config file to use is connect, but can be passed another
		* in the $con argument.
		*
		* Uses that data to create a new PHP Data Object
		*
		* @method __construct
		* @param  string      $con [.ini file to use for database credentials]
		* @example $pdo = new \shgysk8zer0\Core\PDO()
		*/
		public function __construct($con = 'connect')
		{
			parent::__construct($con);
		}
	}
