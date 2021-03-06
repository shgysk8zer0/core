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

use \shgysk8zer0\Core_API as API;

/**
 * An Object-Oriented autoloader for namespaced functions that functions similarly
 * to `spl_autoload` in that it converts namespaces to lower case paths, automatically
 * adding ".php" extension, and loads this from the include path.
 *
 * @example NamespacedFunction::load('\Namespace')->function($args...);
 * @example NamespacedFunction::{'\Namespace\Function'}($args...);
 * @example call_user_func(['NamespacedFunction', '\Namespace\Function'], $arg[1]...);
 *
 * Where all example perform:
 * `require_once "$namespace.php"`;
 * `\$namespace\$function($args[1], ...)`
 */
final class NamespacedFunction implements API\Interfaces\toString
{
	use API\Traits\Singleton;
	const NS = '\\';

	/**
	 * The given namespace
	 * @var string
	 */
	private $_namespace = '';

	/**
	 * The file path as converted from the namespace
	 * @var string
	 */
	private $_path = '';

	/**
	 * Value returned from `require_once`
	 * @var mixed
	 */
	private $_ret_val = null;

	/**
	 * Create an instance and load the file, if required
	 * Use of `::load` via \shgysk8zer0\Core_API\Traits\Singleton is preferred.
	 *
	 * @param string $namespace The file's namespace which must relate to its path
	 */
	public function __construct($namespace)
	{
		$this->_escaped_ns = preg_quote(self::NS, '/');
		$this->_normalizeNS($namespace);
		$this->_namespace = $namespace;
		$this->_path = $this->_getPath($namespace);
		if (file_exists($this->_path)) {
			$this->_ret_val = include_once $this->_path;
		} else {
			throw new \Exception(sprintf('"%s": failed to open stream: No such file or directory', $this->_path));
		}
	}

	/**
	 * Get the namespaced function name as a string or a namespaced constat
	 *
	 * @param  string $name The name of the function or constant
	 * @return mixed		Either the function as string or the value of the constant, or false on failure
	 */
	public function __get($name)
	{
		$name = $this . self::NS . $name;
		if (function_exists($name)) {
			return $name;
		} elseif (defined($name)) {
			return constant($name);
		} else {
			return false;
		}
	}

	/**
	 * Returns true if function exists in script
	 *
	 * @param  string  $function Name of function
	 * @return boolean		   If it exists in the script/namespace
	 */
	public function __isset($name)
	{
		return $this->__get($name) !== false;
	}

	/**
	 * Returns the namespace of the loaded script
	 *
	 * @return string Namespace
	 */
	public function __toString()
	{
		return $this->_namespace;
	}

	/**
	 * Calls the function returned by `include_once`,if any, or throws an exception
	 *
	 * @param  mixed   [...] Accepts any arguments. Up to returned function.
	 * @return mixed   Return of the function
	 */
	public function __invoke()
	{
		if (is_callable($this->_ret_val)) {
			return call_user_func_array($this->_ret_val, func_get_args());
		} else {
			throw new \Exception(sprintf('%s did not return a function.', $this->_namespace));
		}
	}

	/**
	 * Calls a function from within the namespaced PHP script
	 *
	 * @param  string $function The function to call
	 * @param  array  $args	 Array of arguments to pass to it
	 * @return mixed			The return of the funciton
	 */
	public function __call($function, Array $args = array())
	{
		if ($this->__isset($function)) {
			return call_user_func_array($this->__get($function), $args);
		} else {
			throw new \Exception(sprintf(
				"function '%s' not found or invalid function name in script '%s'",
				$function, $this->_path
			));
		}
	}

	/**
	 * Static method to call functions from namespaced scripts, loading file if necessary
	 *
	 * @param  string $namespace_func `\Namespace\Function`
	 * @param  Array  $args		   Array of arguments to pass
	 * @return mixed				  The return of the function
	 */
	public static function __callStatic($namespace_func, Array $args)
	{
		$namespace = explode(self::NS, $namespace_func);
		$function = array_pop($namespace);
		$namespace = join(self::NS, $namespace);
		return static::load($namespace)->__call($function, $args);
	}

	/**
	* Returns the absolute path converted from namespace, based on `DOCUMENT_ROOT`
	* @param  string $namespace The namespace to use {\Namespace}
	* @return string			The converted path {/abs_path/to/namespace.php}
	*/
	private function _getPath($namespace)
	{
		$namespace = trim($namespace, self::NS);
		$script = strtolower(str_replace(self::NS, '/', $namespace));
		if (! pathinfo($script, PATHINFO_EXTENSION)) {
			$script .= '.php';
		}
		return $script;
	}

	/**
	* Handles inconsistencies in namespaces, such as whether or not it begins with "\"
	*
	* @param  string $namespace The given namespace by reference
	* @return void
	*/
	private function _normalizeNS(&$namespace)
	{
		$namespace = self::NS . trim($namespace, self::NS);
	}
}
