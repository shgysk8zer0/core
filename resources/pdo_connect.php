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
	class PDO_Connect extends \PDO
	{
		protected $connect;
		protected static $instances = [];
		public $connected = false;
		public static $ext = 'ini';

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
		 * @method __construct
		 * @desc
		 * Gets database connection info from /connect.ini (using parse_ini_file)
		 * The default ini file to use is connect, but can be passed another
		 * in the $con argument.
		 *
		 * Uses that data to create a new PHP Data Object
		 *
		 * @param mixed $con (.ini file to use for database credentials)
		 * @return void
		 * @uses \shgysk8zer0\Core\resources\Parser
		 * @example parent::__construct($con)
		 */
		public function __construct($con = 'connect')
		{
			try{
				if (is_string($con)) {
					$tmp_ext = Parser::$DEFAULT_EXT;
					Parser::$DEFAULT_EXT = $this::$ext;
					$this->connect = Parser::parse($con);
					Parser::$DEFAULT_EXT = $tmp_ext;
					unset($tmp_ext);
				} elseif (is_object($con)) {
					$this->connect = $con;
				} elseif (is_array($con)) {
					$this->connect = (object)$con;
				}

				if (is_null($this->connect) or !is_object($this->connect)) {
					throw new \Exception('Unable to parse credentials.');
				} elseif (is_null($this->connect->user)) {
					throw new \Exception('User not given in credentials');
				} elseif (is_null($this->connect->password)) {
					throw new \Exception('Password not given in credentials');
				}

				if (
					isset($this->connect->server)
					and array_key_exists('SERVER_ADDR', $_SERVER)
					and $this->connect->server === $_SERVER['SERVER_ADDR']
				) {
					unset($this->connect->server);
				}

				if (is_null($this->connect->database)) {
					$this->connect->database = $this->connect->user;
				}

				if (
					isset($this->connect->port)
					and (
						!isset($this->connect->server)
						or $this->connect->server === $this::DEFAULT_SERVER
					)
				) {
					unset($this->connect->port);
				}

				$connect_string = (isset($this->connect->type)) ? "{$this->connect->type}:" : 'mysql:';
				$connect_string .= "dbname={$this->connect->database}";

				if (isset($this->connect->server)) {
					$connect_string .= ";host={$this->connect->server}";
				}

				if (
					isset($this->connect->port)
					and isset($this->connect->server)
					and $this->connect->server !== $this::DEFAULT_SERVER
				) {
					$connect_string .= ";port={$this->connect->port}";
				}

				parent::__construct(
					$connect_string,
					"{$this->connect->user}",
					"{$this->connect->password}"
				);

				$this->connected = true;
			} catch(\Exception $e) {
				@file_put_contents(
					self::LOG_DIR . DIRECTORY_SEPARATOR . __CLASS__ . '.log',
					"{$e}" . PHP_EOL,
					FILE_APPEND | LOCK_EX
				);
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
				$fname = BASE . DIRECTORY_SEPERATOR . $this->connect->database;
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
				$filename = BASE . DIRECTORY_SEPARATOR . $this->connect->database . '.sql';
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
				$command = 'mysqldump -u ' . escapeshellarg($this->connect->user);

				if (isset($this->connect->server) and $this->connect->server !== $this::DEFAULT_SERVER) {
					$command .= ' -h ' . escapeshellarg($this->connect->server);
				}

				$command .= ' -p' . escapeshellarg($this->connect->password);

				$command .=  ' ' . escapeshellarg($this->connect->database);

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
