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
namespace shgysk8zer0\Core\Resources;

/**
 * Easily parse any INI, JSON, or XML file
 *
 * @example $parsed = \shgysk8zer0\Core\Resources\Parser::parseFile('file')
 * @example $parsed = \shgysk8zer0\Core\Resources\Parser::parseFile('path/to/file.ext')
 * @example $parsed = new \shgysk8zer0\Core\Resorources\Parser(*)
 */
use \shgysk8zer0\Core_API as API;

final class Parser implements
	API\Interfaces\File_Resources,
	API\Interfaces\Magic_Methods,
	API\Interfaces\Path_Info
{
	use API\Traits\Parser;
	use API\Traits\Magic_Methods;
	use API\Traits\Singleton;

	const MAGIC_PROPERTY = '_data';

	/**
	 * Private var to store parsed data
	 * @var array
	 */
	private $_data = [];

	/**
	 * Static method to parse file & return parsed contents
	 *
	 * @param string $file Path to file, using include_path
	 * @return array       Parsed file contents
	 */
	public static function parseFile($file)
	{
		return (object)static::load($file)->{self::MAGIC_PROPERTY};
	}

	/**
	 * Class constructor parses $file and stores data in $data array.
	 *
	 * @param string $file Path to file, using include_path
	 */
	public function __construct($file)
	{
		$this->{$this::MAGIC_PROPERTY} = $this->parse($file, true);
	}
}
