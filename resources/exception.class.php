<?php
	/**
	 * Extend \Exception to make its protected vars public
	 *
	 * @author Chris Zuber <shgysk8zer0@gmail.com>
	 * @package core_shared
	 * @version 2014-12-01
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
	 * @link http://php.net/manual/en/class.exception.php
	 */

	namespace shgysk8zer0\core\resources;
	class Exception extends \Exception {
		public $line, $file, $message, $code, $trace;
		const LOG_DIR = 'exception_logs';

		/**
		 * Create the Exception
		 *
		 * Most information about it ($line, $file, $trace) is set automatically
		 * @param string $message       [Message given for the exception]
		 * @param int $code             [Error code for the Exception]
		 * @param \Exception $prev      [Previous Exception thrown]
		 */

		public function __construct($message, $code = null, \Exception $prev = null) {
			parent::__construct($message, $code, $prev);
			$this->trace = $this->getTrace();
		}

		/**
		 * Save Exceptions including stack trace to a log file
		 * 
		 * @param  string $fname [filename (no extension) to write to]
		 * @return void
		 */

		public function log($fname) {
			file_put_contents(
				BASE . DIRECTORY_SEPARATOR . $this::LOG_DIR . DIRECTORY_SEPARATOR . $fname . '.log',
				"{$this}" . PHP_EOL,
				FILE_APPEND | LOCK_EX
			);
		}
	}
?>
