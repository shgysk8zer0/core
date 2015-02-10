<?php
/**
* Wrapper for standard PDO class.
*
* This class is meant only to be extended and
* not used directly. It offers only a protected
* __construct method and a public escape.
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
*/

namespace shgysk8zer0\Core\Interfaces;
interface Legacy_PDO
{
	/**
	* @method __construct
	* @desc
	* Gets database connection info from connect.ini (using ini::load)
	* The default ini file to use is connect, but can be passed another
	* in the $con argument.
	*
	* Uses that data to create a new PHP Data Object
	*
	* @param string $con (.ini file to use for database credentials)
	* @return void
	* @example parent::__construct($con)
	*/

	/**
	* For lack of a good ol' escape method in PDO.
	*
	* @param string $str
	* @return string
	*/
	public function quote($str);

	/**
	* Converts array_keys to something safe for
	* queries. Returns an array of the converted keys
	*
	* @param array $arr
	* @return array
	*/
	public function columns(array $arr);

	/**
	* Converts array_keys to something safe for
	* queries. Returns the same array with converted keys
	*
	* @param array $arr
	* @return array
	*/
	public function prepare_keys(array $arr);

	/**
	* Maps passed array_keys into keys suitable for binding,
	* E.G. "some key" becomes "some_key"
	* @param  array  $arr [Full array, though only keys will be used]
	* @return array       [Indexed array created from array_keys]
	*/
	public function bind_keys(array $arr);

	/**
	* Restore database connection from a ".sql" file
	* @param  string $fname [SQL file without the extension]
	* @return bool          [Whether or not the restore query was successful]
	*/
	public function restore($fname = null);

	/**
	* Does a mysqldump and outputs to $filename
	* @param  string $filename [Name of file to output to]
	* @return bool             [Whether or not dump was successful]
	*/
	public function dump($filename = null);

	/**
	* Returns a 0 indexed array of tables in database
	*
	* @param void
	* @return array     [Array containing all tables in database]
	*/
	public function show_tables();

	/**
	* Returns a 0 indexed array of tables in database
	*
	* @param void
	* @return array    [Array containing database names]
	*/
	public function show_databases();

	/**
	* Describe $table, including:
	* Field {name}
	* Type {varchar|int... & (length)}
	* Null (boolean)
	* Default {value}
	* Extra {auto_increment, etc}
	*
	* @param string $table
	* @return array
	* @depreciated
	*/
	public function describe($table);

	/**
	* Converts array keys into MySQL columns
	* [
	* 	'user' => 'me',
	* 	'password' => 'password'
	* ]
	* becomes '`user`, `password`'
	*
	* @param array $array
	* @return string
	*/
	public function columns_from(array $array);

	public function fetch_array($query, $col = null);
}
