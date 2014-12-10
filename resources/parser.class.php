<?php
	/**
	 * Easily parse any INI, JSON, or XML file
	 *
	 * Uses include_path for any file, so you don't have to remember exactly where
	 * a file is located, so long as it is included in include_path
	 *
	 * If no extension is given, it defaults to one set via a public static var
	 *
	 * This means that, if you don't remember where you placed a config file or what
	 * extension it had, but know that it uses the default extension and was in the
	 * include_path, you can find and parse it just by knowing its filename
	 *
	 * @author Chris Zuber <shgysk8zer0@gmail.com>
	 * @package core_shared
	 * @version 2.3
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
	 * @var string $filename [Name of file without directory or extension]
	 * @var string $path     [Resolved absolute path of directory to file]
	 * @var string $subpath  [Directory portion of $file, relative to include_path]
	 * @var string $ext      [The file extension, defaulting to static::$DEFAULT_EXT]
	 * @var mixed  $data     [Parsed contents of $filename]
	 * @var string $file     [Resolved, absolute full path to file]
	 * @var bool   $found    [Whether or not the file exists]
	 * @var array  $files    [Static array of loaded files]
	 * @var array  $exts     [Static array of supported file extensions]
	 *
	 * @example $parsed = \core\resources\Parser::parse('file')
	 * @example $parsed = \core\resources\Parser::parse('file.ext')
	 * @example $parsed = \core\resources\Parser::parse('path/to/file.ext')
	 * @example $parsed = new \core\resorources\Parser(*)
	 */

	namespace core\resources;
	final class Parser {
		private $filename, $path, $subpath, $ext, $data;
		public $file, $found = false;
		public static $DEFAULT_EXT = 'ini', $logging = false;
		private static $files = null, $exts = ['ini', 'json', 'xml'];
		const LOG_FILE = 'parser.log';

		/**
		 * Parse a file using the static method to return its parsed data
		 *
		 * All files parsed by this are stored in the static $files array, so
		 * that a file needs only be loaded and parsed a single time.
		 *
		 * @param  string $file [file, with or without ext & path]
		 * @return mixed        [\stdClass Parsed data or null]
		 */

		public static function parse($file) {
			if(!is_array(self::$files)) {
				self::$files = [];
			}
			if(!in_array($file, self::$files)) {
				self::$files[$file] = new self($file);
			}
			return self::$files[$file]->data;
		}

		/**
		 * Load & parse a file by standard means
		 *
		 * Unlike the static parse method, parsing a file by this means does not
		 * return the parsed contents of the file. Instead, it creates a new
		 * instance of Parser, and the full contents must already be known
		 * because $data is a private variable. $data is still accessible
		 * through PHP's magic methods, but you must know ahead of time the
		 * structure of the file which you requested.
		 *
		 * @param string $file [File, with or without ext & path]
		 */

		public function __construct($file) {
			try {
				$this->filename = pathinfo($file, PATHINFO_FILENAME);
				$this->subpath = pathinfo($file, PATHINFO_DIRNAME);
				$this->subpath =($this->subpath === '.') ? null :  $this->subpath . DIRECTORY_SEPARATOR;
				$this->ext = pathinfo($file, PATHINFO_EXTENSION);
				if(!$this->ext) {
					$this->ext = $this::$DEFAULT_EXT;
				}
				$this->ext = "{$this->ext}";
				$this->path = dirname(stream_resolve_include_path("{$this->subpath}{$this->filename}.{$this->ext}"));
				$this->file = $this->path . DIRECTORY_SEPARATOR . $this->filename . '.' . $this->ext;
				$this->found = @file_exists($this->file);

				if(!$this->found) {
					throw new \Exception("File: {$file} was not found");
				}
				elseif(!is_readable($this->file)) {
					throw new \Exception("{$this->file} was found but could not be read");
				}
				else {
					$this->read();
				}
			}
			catch(\Exception $e) {
				$this->log($e);
			}
		}

		/**
		 * Protected logging method
		 *
		 * Used internally for logging such things as file not found, unabled to
		 * read file, unsupported extension, etc.
		 *
		 * Appends $this::LOG_FILE with filename, time, and full exception
		 * details.
		 *
		 * @param  Exception $e [The exception that was thrown]
		 * @return void
		 */

		protected function log(\Exception $e) {
			file_put_contents(
				__DIR__ . DIRECTORY_SEPARATOR . $this::LOG_FILE,
				'@' . date('Y-m-d\TH:i:s', time()) . "\t" . $this->file . PHP_EOL . "{$e}" . PHP_EOL . PHP_EOL,
				FILE_APPEND | LOCK_EX
			);
		}

		/**
		 * Where file parsing takes place
		 *
		 * Do a switch on extension to determine which function to use to parse
		 * the file, defaulting to throwing an exception of an unsupported
		 * format.
		 *
		 * @param void
		 * @return void
		 */

		protected function read() {
			if($this->found) {
				try {
					switch(strtolower($this->ext)) {
						case 'ini': {
							$this->data = (object)parse_ini_file($this->file);
						} break;

						case 'json': {
							$this->data = json_decode(file_get_contents($this->file));
						} break;

						case 'xml': {
							$this->data = simplexml_load_file($this->file);
						} break;

						default: {
							throw new \Exception("No not know how to parse files with extension {$this->ext}");
						}
					}
					if(empty($this->data)) {
						throw new \Exception("{$this->file} was found but could not be parsed");
					}
				}
				catch(\Exception $e) {
					if($this::$logging) {
						$this->log($e);
					}
				}
			}
		}

		/**
		 * Magic setter method for the class
		 *
		 * Sets property $key in $this->data to $value
		 *
		 * @param string $key   [The property to set]
		 * @param mixed $value  [The value to set it to]
		 *
		 * @example $parsed->$key = $value
		 */

		public function __set($key, $value) {
			$this->data{$key} = $value;
		}

		/**
		 * Magic getter method for the class
		 *
		 * Returns the value of $this->data->$key
		 *
		 * @param  string $key [The property to get]
		 * @return mixed       [The value of $this->data->$key]
		 *
		 * @example $test = $parsed->$key
		 */

		public function __get($key) {
			return $this->data->{$key};
		}

		/**
		 * Magic isset method for the class
		 *
		 * Check whether or not a property is set in $this->data
		 * @param  string  $key [The property in $this->data]
		 * @return boolean      [Whether or not it is set]
		 *
		 * @example isset($parsed->$key)
		 */

		public function __isset($key) {
			return isset($this->data->{$key});
		}

		/**
		 * Magic unset method for the class
		 *
		 * Unset $key from $this->data
		 *
		 * @param string $key [Property to unset from $this->data]
		 * @return void
		 *
		 * @example unset($parsed->$key)
		 */

		public function __unset($key) {
			unset($this->data->{$key});
		}

		/**
		 * Save updated file contents back to their original filest
		 *
		 * @return void
		 *
		 * @todo Make work will all supported extensions (No INI support yet)
		 */

		public function save() {
			try {
				if($this->found) {
					switch(strtolower($this->ext)) {
						case 'json': {
							file_put_contents($this->file, json_encode($this->data), JSON_PRETTY_PRINT);
						} break;

						case 'xml': {
							$this->data->asXml($this->file);
						} break;

						default: {
							throw new \Exception("Do not know how to write to a {$this->ext} file");
						}
					}
				}
				else {
					throw new \Exception('Trying to save to a file which does not exist');
				}
			}
			catch(\Exception $e) {
				$this->log($e);
			}
		}
	}
?>
