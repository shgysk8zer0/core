<?php
/**
 * @author Chris Zuber
 * @package shgysk8zer0\Core
 * @version 1.0.0
 * @copyright 2016, Chris Zuber
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
 * Class for validating and working with uploaded files
 */
 class UploadFile extends \ArrayObject
 {
	/**
	 * Create a new instance
	 * @param string $key Key, as found in `$_FILES`
	 */
	public function __construct($key)
	{
		if (array_key_exists($key, $_FILES)) {
			parent::__construct($_FILES[$key], self::ARRAY_AS_PROPS);
			if (! isset($this->name, $this->size, $this->error, $this->tmp_name)) {
				throw new \InvalidArgumentException("$key is not a valid file upload.");
			} elseif (!is_uploaded_file($this->tmp_name)) {
				throw new \InvalidArgumentException('File is not an uploaded file.');
			} elseif (!$this->_checkFile()) {
				trigger_error("{$this->name} does not match uploaded file.", \E_USER_ERROR);
			} else {
				$this->_checkError();
			}
		} else {
			throw new \InvalidArgumentException("$key not found in _FILES");
		}
	}

	/**
	 * Returns JSON encoded version of file data
	 * @param void
	 * @return string JSON string
	 */
	public function __toString()
	{
		return json_encode($this->getArrayCopy());
	}

	/**
	 * Moves uploaded file into $dir directory
	 * @param  array  $dir ('path', 'to', save)
	 * @return boolean      Whether or not the move was successful
	 */
	public function saveTo(...$path)
	{
		$dir = join(DIRECTORY_SEPARATOR, $path);
		$path = join('/', $path);
		$dir = trim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		$dir = $_SERVER['DOCUMENT_ROOT'] . $dir;
		if (!is_dir($dir)) {
			mkdir($dir, 0755, true) or trigger_error("Error creating directory, $dir.");
		}
		if (move_uploaded_file($this->tmp_name, $dir . $this->name)) {
			$this->path = "/{$path}/{$this->name}";
			return true;
		}
		return false;
	}

	/**
	 * Checks whether the uploaded file matches MIME and size
	 * @param void
	 * @return boolean Whether or not they match
	 */
	private function _checkFile()
	{
		$info = new \Finfo();
		return $info->file($this->tmp_name, \FILEINFO_MIME_TYPE) === $this->type
			and filesize($this->tmp_name) === $this->size;
	}

	/**
	 * Checks for upload errors. Triggers error if not `UPLOAD_ERR_OK`.
	 * @param void
	 * @return void
	 */
	private function _checkError()
	{
		switch($this->error) {
			case UPLOAD_ERR_OK:
				break;
			case UPLOAD_ERR_INI_SIZE:
				trigger_error("{$this->name} exceeds max upload size set in php.ini");
				break;
			case UPLOAD_ERR_FORM_SIZE:
				trigger_error("{$this->name} exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.");
				break;
			case UPLOAD_ERR_PARTIAL:
				trigger_error("{$this->name} was only partially uploaded.");
				break;
			case UPLOAD_ERR_NO_FILE:
				trigger_error("No file was uploaded");
				break;
			case UPLOAD_ERR_NO_TMP_DIR:
				trigger_error("Missing a temporary folder. Could not upload {$this->name}.");
				break;
			case UPLOAD_ERR_CANT_WRITE:
				trigger_error("Failed to write {$this->name} to disk.");
				break;
			case UPLOAD_ERR_EXTENSION:
				trigger_error('A PHP extension stopped the file upload.');
				break;
			default:
				trigger_error('Unknown file upload error.');
		}
	}
 }
