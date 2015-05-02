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
 * Class for easily reading and writing generic file type, defaulting to PHP://temp
 * Provides a few magic methods to allow writing when used as a function and
 * reading when used as a string
 */
class File implements API\Interfaces\File_Resources, API\Interfaces\String
{
	use API\Traits\File_Resources;
	use API\Traits\Singleton;
	use API\Traits\Syntax_Highlighter;

	const TEMP_FILE    = 'php://temp';
	const DEFAULT_MODE = 'a+';

	/**
	 * Create the file instance, open the file and set a lock on it
	 *
	 * @param string $filename         Filename or URL
	 * @param string $mode             Type of access required to the stream
	 * @param bool   $use_include_path Use include path?
	 */
	public function __construct(
		$filename         = self::TEMP_FILE,
		$use_include_path = false,
		$mode             = self::DEFAULT_MODE
	)
	{
		$this->fopen($filename, $use_include_path, $mode);
		$this->flock(LOCK_EX);
	}

	/**
	 * Release lock on file and close it when class is destroyed
	 */
	final public function __destruct()
	{
		$this->flock(LOCK_UN);
		$this->fclose();
	}

	/**
	 * Writes to file when class used as a function
	 *
	 * @param  string $string Content to add to file
	 * @return int            Number of bytes written
	 */
	final public function __invoke($string)
	{
		return $this->filePutContents($string . PHP_EOL, FILE_APPEND);
	}

	/**
	 * Returns the entire file's contents when class used as a string
	 *
	 * @return string file_get_contents
	 */
	final public function __toString()
	{
		return $this->fileGetContents();
	}
}
