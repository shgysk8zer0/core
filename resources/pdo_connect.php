<?php
	namespace shgysk8zer0\Core\resources;
	use \shgysk8zer0\Core\resources\Parser as Parser;

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
	class PDO_Connect extends \shgysk8zer0\Core\Abstracts\PDO_Connect
	{
		protected static $instances = [];

		const DEFAULT_SERVER = 'localhost';
		const LOG_DIR = 'logs';


		/**
		 * Static class load method
		 *
		 * Creates a new instance and returns it if one does not exist,
		 * otherwise returns the existing instance
		 *
		 * @param mixed $con
		 * @return
		 */
		public static function load($con = 'connect')
		{
			if (is_string($con)) {
				if (!array_key_exists($con, self::$instances)) {
					self::$instances[$con] = new self($con);
				}
				return self::$instances[$con];
			} else {
				return new self($con);
			}
		}

		/**
		 * Writes errors to a log file
		 *
		 * @param string $method
		 * @param int $line
		 * @param string $message
		 * @return void
		 */
		protected function log($method = null, $line = null, $message = '')
		{
			file_put_contents(
				BASE . DIRECTORY_SEPARATOR . __CLASS__ . '.log',
				"Error in $method in line $line: $message" . PHP_EOL,
				FILE_APPEND | LOCK_EX
			);
		}

		/**
		 * Restores a MySQL database from file $fname
		 *
		 * @param string $fname
		 * @return self
		 */
		public function restore($fname = null)
		{
			if (is_null($fname)) {
				$fname = BASE . DIRECTORY_SEPERATOR . $this->database;
			}

			$sql = file_get_contents("{$fname}.sql");
			if (is_string($sql)) {
				return $this->query($sql);
			} else {
				return false;
			}
		}

		/**
		 * Does a mysqldump if permissions allow
		 *
		 * Return value is based on whether or not permissions
		 * allow file to be written, not whether or not it was.
		 *
		 * Default filename is the name of the database
		 * from connection
		 *
		 * @param string $filename
		 * @return boolean
		 */
		public function dump($filename = null)
		{
			if (is_null($filename)) {
				$filename = BASE . DIRECTORY_SEPARATOR . $this->database . '.sql';
			}

			if (
				(
					file_exists($filename)
					and is_writable($filename)
				) or (
					!file_exists($filename)
					and is_writable(BASE)
				)
			) {
				$command = 'mysqldump -u ' . escapeshellarg($this->user);

				if (isset($this->server) and $this->server !== $this::DEFAULT_SERVER) {
					$command .= ' -h ' . escapeshellarg($this->server);
				}

				$command .= ' -p' . escapeshellarg($this->password);

				$command .=  ' ' . escapeshellarg($this->database);

				exec($command, $output, $return_var);
				if ($return_var === 0 and is_array($output) and !empty($output)) {
					file_put_contents($filename, join(PHP_EOL, $output));
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
	}
