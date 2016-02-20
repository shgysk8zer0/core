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

/**
 * Class to register and configure autoloaders
 */
final class Autoloader
{
	use \shgysk8zer0\Core_API\Traits\GetInstance;

	/**
	 * Callback to call when auto-loading a new class
	 *
	 * @var Callback
	 */
	private $_autoloader;

	/**
	 * Create instance and configure autoloader
	 *
	 * @param mixed    $path     Path to class directory or boolean to include current path
	 * @param Callable $callback Function to call to autoload classes
	 * @param array    $exts     Array of extensions to use when searching for class files
	 */
	public function __construct(
		Callable $callback,
		$path = false,
		array $exts = array('.php')
	)
	{
		$this->_autoloader = $callback;
		if (is_string($path)) {
			set_include_path(realpath($path) . PATH_SEPARATOR . get_include_path());
		} elseif ($path === true) {
			set_include_path(dirname(dirname(__DIR__)) . PATH_SEPARATOR . get_include_path());
		}

		spl_autoload_extensions(join(',', $exts));
		spl_autoload_register($this);
	}

	/**
	 * Called whenever class instance used as a function
	 *
	 * @param  string $class Name of class to load
	 *
	 * @return void
	 */
	public function __invoke($class)
	{
		call_user_func($this->_autoloader, $class);
	}
}
