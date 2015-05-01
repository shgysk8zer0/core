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
 * Provides easy implementation of error reporting though several methods
 */
final class Errors implements API\Interfaces\File_Resources
{
	use API\Traits\Singleton;
	use API\Traits\File_Resources;

	// List of methods available for a variety of uses
	const LOG_METHOD     = 'logErrorException';
	const DB_METHOD      = 'DBErrorException';
	const CONSOLE_METHOD = 'consoleErrorException';

	// Constants useful for creating file handles and database queries
	const LOG_FILE = 'errors.log';
	const FILE_MODE = 'a+';
	const PDO_QUERY = 'INSERT INTO `errors` (
		`message`,
		`code`,
		`severity`,
		`file`,
		`line`,
		`trace`
	) VALUES (
		:message,
		:code,
		:severity,
		:file,
		:line,
		:trace
	);';

	/**
	 * Prepared statement to execute with ErrorException data
	 * @var \PDOStatement
	 */
	private $error_stm;

	/**
	 * Array of keys to bind to when executing $error_stm
	 * @var array
	 */
	private $binders = array(
		'message'  => 'message',
		'code'     => 'code',
		'severity' => 'severity',
		'file'     => 'file',
		'line'     => 'line',
		'trace'    => 'trace'
	);

	/**
	 * Method to use when class used as function in __invoke
	 * @var Callable/array
	 */
	private $__invoke_method;

	/**
	 * Creates a new instance of Errors class
	 *
	 * @param string $default_method Method to call in __invoke
	 */
	public function __construct($default_method = self::DB_METHOD)
	{
		if (method_exists($this, $default_method)) {
			$this->__invoke_method = [$this, $default_method];
		} else {
			$this->__invoke_method = [$this, self::DB_METHOD];
			trigger_error(sprintf('No method %s exists in %s', $default_method, __CLASS__));
		}
	}

	/**
	 * Release lock and close file when class is destroyed
	 */
	public function __destruct()
	{
		if (is_resource($this->fhandle)) {
			$this->flock(LOCK_UN);
			$this->fclose();
		}
	}

	/**
	 * Registers the PDOStatement to execute in DBErrorException
	 *
	 * @param PDOStatement $stm     Prepared statement to store errors to database
	 * @param array        $binders Array or keys to bind to when executing $stm
	 * @return self
	 */
	public function registerPDOStatement(\PDOStatement $stm = null, array $binders = array())
	{
		if (is_null($stm)) {
			$this->error_stm = PDO::load(PDO::DEFAULT_CON)->prepare(self::PDO_QUERY);
		} else {
			$this->error_stm = $stm;
		}
		if (
			is_array($binders) and array_keys($binders) === array_keys($this->binders)
		) {
			$this->binders = $binders;
		}
		return $this;
	}

	/**
	 * Sets the file to record ErrorExceptions to
	 *
	 * @param API\Interfaces\File_Resources $file Class to use when logging errors to file
	 * @return self
	 */
	public function registerLogFile(
		$filename         = self::LOG_FILE,
		$use_include_path = false
	)
	{
		if (is_resource($this->fhandle)) {
			$this->flock(LOCK_UN);
			$this->fclose();
		}
		$this->fopen($filename, $use_include_path, self::FILE_MODE);
		$this->flock(LOCK_EX);
		return $this;
	}

	/**
	 * Log ErrorExceptions to file
	 *
	 * @param ErrorException $err_exc The error exception
	 * @return void
	 */
	public function logErrorException(\ErrorException $err_exc)
	{
		if (! is_resource($this->fhandle)) {
			$this->registerLogFile();
		} else {
			$this->filePutContents(PHP_EOL . $err_exc . PHP_EOL, FILE_APPEND);
		}
	}

	/**
	 * Store Error exceptions to database from prepared statement
	 *
	 * @param ErrorException $err_exc The error exception
	 * @return void
	 */
	public function DBErrorException(\ErrorException $err_exc)
	{
		if (! $this->error_stm instanceof \PDOStatement) {
			$this->registerPDOStatement();
		}
		$this->error_stm->{$this->binders['message']}  = $err_exc->getMessage();
		$this->error_stm->{$this->binders['code']}     = $err_exc->getCode();
		$this->error_stm->{$this->binders['severity']} = $err_exc->getSeverity();
		$this->error_stm->{$this->binders['file']}     = $err_exc->getFile();
		$this->error_stm->{$this->binders['line']}     = $err_exc->getLine();
		$this->error_stm->{$this->binders['trace']}    = $err_exc->getTraceAsString();
		$this->error_stm->execute();
	}

	/**
	 * Log error exceptions to user console using JSON_Response::error
	 *
	 * @param ErrorException $err_exc The error exception
	 * @uses JSON_Response
	 */
	public function consoleErrorException(\ErrorException $err_exc)
	{
		JSON_Response::load()->error([
			'message'   => $err_exc->getMessage(),
			'code'      => $err_exc->getCode(),
			'severity'  => $err_exc->getSeverity(),
			'line'      => $err_exc->getLine(),
			'file'      => $err_exc->getFile(),
			'trace'     => $err_exc->getTrace()
		]);
	}

	/**
	 * When class called as function, pass arguments along to $this->__invoke_method
	 *
	 * @param  ErrorException $error_exc The error exception
	 * @return void
	 */
	public function __invoke(\ErrorException $error_exc)
	{
		call_user_func($this->__invoke_method, $error_exc);
	}
}
