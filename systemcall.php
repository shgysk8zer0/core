<?php
/**
 * @author Chris Zuber
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
 * Class providing saef and easy access to system commands
 * @example $git = new \shgysk8zer0\Core\SystemCall('git', ['status']);
 * $output = [];
 * $return_var = null;
 * $last_line = $git($output, $return_var);
 */
final class SystemCall implements API\Interfaces\toString
{
	/**
	 * Private var to store the command string in
	 *
	 * @var string
	 */
	private $_call = '';

	/**
	 * Array of characters to replace in argument names
	 *
	 * @var array
	 */
	private $_replace_chars = array(' ', '_');

	use API\Traits\Singleton;

	/**
	 * Create a new instance of a system call
	 *
	 * @param string $func     Name of system function to call
	 * @param array  $commands Array of non-flag arguments
	 * @param array  $args     Array of flags to set
	 */
	public function __construct(
		$func,
		array $commands = array(),
		array $args = array()
		)
	{
		if (! is_string($func)) {
			throw new \InvalidArgumentException(sprintf(
				'%s expects $func to be a string, got a %s',
				__METHOD__,
				gettype($func))
			);
		}

		$this->_call = strtok($func, ' ');

		array_walk($commands, [$this, '_escapeCommand']);

		if (! empty($commands)) {
			$this->_call .= ' ' . join(' ', $commands);
		}

		array_map([$this, '__set'], array_keys($args), array_values($args));
	}

	/**
	 * Automatically set -f or --flag
	 * Escapes any arguments
	 *
	 * @param string $flag  The "-f" or "--flag"
	 * @param mixed  $value Optional value to set it to
	 * @return void
	 */
	public function __set($flag, $arg = null)
	{
		$this->_call .= $this->_setFlag($flag);

		if ($this->_isArg($arg)) {
			$this->_escapeArg($arg);
			$this->_call .= ' ' . $arg;
		}
	}

	/**
	 * Chainable method for setting flags
	 *
	 * @param  string $flag The "-f" or "--flag"
	 * @param  array  $args Array of values to join for the flag's value
	 *
	 * @return self
	 */
	public function __call($flag, array $args = array(''))
	{
		if (empty($args)) {
			$this->__set($flag, null);
		}
		foreach ($args as $arg) {
			$this->__set($flag, $arg);
		}
		return $this;
	}

	/**
	 * Get the system call as an escaped string
	 *
	 * @param void
	 *
	 * @return string The escaped string for the command
	 */
	public function __toString()
	{
		return escapeshellcmd($this->_call);
	}

	/**
	 * Executes the system call using `exec`
	 *
	 * @param  array  $output     Array to be filled with lines of output
	 * @param  int   $return_var  Set to the return status of the executed command
	 *
	 * @return string             Last line of $output
	 * @see https://secure.php.net/manual/en/function.exec.php
	 */
	public function __invoke(array& $output = array(), &$return_var = null)
	{
		return exec($this, $output, $return_var);
	}

	/**
	 * Checks for strings, numeric values, and booleans
	 *
	 * @param  mixed $arg  The value to check
	 *
	 * @return bool        Whether or not it is a valid arg (string or numeric)
	 */
	private function _isArg($arg)
	{
		return (is_bool($arg) or is_string($arg) or is_numeric($arg));
	}

	private function _escapeCommand(&$string)
	{
		$string = str_replace($this->_replace_chars, '-', $string);
	}

	/**
	 * Private function to escape or convert flag values
	 *
	 * @param  mixed $arg The given value
	 *
	 * @return void
	 */
	private function _escapeArg(&$arg)
	{
		if (is_bool($arg)) {
			$arg = $arg ? 'true' : 'false';
		} elseif (is_string($arg) and strlen($arg) !== 0) {
			$arg = escapeshellarg($arg);
		} elseif (! is_numeric($arg)) {
			throw new \InvalidArgumentException(sprintf(
				'%s expect $arg to be string-like, received a %s',
				__METHOD__,
				gettype($arg)
			));
			$arg = null;
		}
	}

	/**
	 * Converts $_repalce_chars to '-'
	 *
	 * @param string $string The string to convert and adds '-' or '--' to make flag
	 *
	 * @return  The converted string
	 */
	private function _setFlag($string)
	{
		$this->_escapeCommand($string);
		return (strlen($string) > 1) ?  " --$string" : " -$string";
	}
}
