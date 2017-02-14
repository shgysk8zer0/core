<?php
/**
 * @author Chris Zuber
 * @package shgysk8zer0\Core
 * @version 1.0.0
 * @copyright 2017, Chris Zuber
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
 * Class for validating and working with uploaded files
 */
class UploadFile extends \ArrayObject implements \JSONSerializable
{
	use API\Traits\FileUploads;

	const IMAGE_TYPES = [
		'image/jpeg',
		'image/png',
		'image/gif',
	];

	const AUDIO_TYPES = [
		'audio/x-vorbis+ogg',
		'audio/mpeg',
	];

	const VIDEO_TYPES = [
		'video/webm',
		'video/mp4',
	];

	const DOCUMENT_TYPES = [
		'text/plain',
		'application/pdf',
		'application/rtf',
		'application/msword',
		'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'application/vnd.oasis.opendocument.text',
	];

	const SPREADSHEET_TYPES = [
		'text/csv',
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'application/vnd.oasis.opendocument.spreadsheet',
	];

	const PRESENTATION_TYPES = [
		'application/vnd.openxmlformats-officedocument.presentationml.presentation',
		'application/vnd.oasis.opendocument.presentation',
	];

	const WEB_TYPES = [
		'text/html',
		'application/json',
		'text/css',
		'application/javascript',
		'text/x-log',
		'application/sql',
		'text/markdown',
	];

	/**
	 * Create a new instance
	 * @param string $key Key, as found in `$_FILES`
	 */
	public function __construct(
		Array  $file,
		Array  $allowed_types = self::IMAGE_TYPES
	)
	{
		if (static::isValidUpload($file, $allowed_types)) {
			parent::__construct($file, self::ARRAY_AS_PROPS);
		} else {
			throw new \InvalidArgumentException('Did not receive a valid file upload');
		}
	}

	/**
	 * Returns JSON encoded version of file data
	 * @param void
	 * @return string JSON string
	 */
	public function __toString()
	{
		return $this->name;
	}

	public function __debugInfo()
	{
		return $this->getArrayCopy();
	}

	public function jsonSerialize()
	{
		return $this->getArrayCopy();
	}

	/**
	 * Moves uploaded file into $dir directory
	 * @param  array  $dir ('path', 'to', save)
	 * @return boolean      Whether or not the move was successful
	 */
	public function saveTo(...$path)
	{
		$dir = rtrim($_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		$dir .= join(DIRECTORY_SEPARATOR, $path) . DIRECTORY_SEPARATOR;
		$path = join('/', $path);

		if (!is_dir($dir)) {
			mkdir($dir, 0755, true) or trigger_error("Error creating directory, $dir.");
		}
		if (move_uploaded_file($this->tmp_name, $dir . $this->name)) {
			$this->path = "/{$path}/{$this->name}";
			return true;
		}
		return false;
	}
}
