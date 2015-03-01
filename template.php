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
 * Opens a template file, ready to be easily modified.
 * File contents are loaded and optionaly minified
 * Placeholders are dynamically replaced.
 * When retrieving output, all data is reset and ready
 * to be used again.
 * @example
 * 	echo Template::load('my_template')->setContent('My content');
 * 	$template = new Template('template_file');
 * 	$template->$placeholder = $value;
 * 	echo $template;
 */
class Template implements API\Interfaces\Magic_Methods
{
	use API\Traits\Singleton;
	use API\Traits\Magic_Methods;
	use API\Traits\File_IO;

	/**
	 * Contents of template file
	 * @var string
	 */
	private $source = '';

	/**
	 * Array containing replacements to make in template
	 * @var array
	 */
	private $replacements = [];
	const MAGIC_PROPERTY = 'replacements';
	const MINIFY_EXPRESSION = '/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/';
	const SUFFIX = '%';
	const PREFIX = '%';
	const TEMPLATES_EXTENSION = '.tpl';
	const TEMPLATES_DIR = 'templates';

	/**
	 * Reads the template specified by $tpl
	 *
	 * @param string $tpl     Path of template, no extension
	 * @param boolean $minify Whether or not to eliminate tabs and newlines
	 * @example $template = new template($template_file)
	 */
	public function __construct($tpl, $minify = true)
	{
		$this->openFile(
			defined('THEME')
				? join(
					DIRECTORY_SEPARATOR,
					[
						BASE,
						'components',
						THEME,
						$this::TEMPLATES_DIR,
						$tpl . $this::TEMPLATES_EXTENSION
					]
				)
				: join(
					DIRECTORY_SEPARATOR,
					[
						BASE,
						'components',
						$this::TEMPLATES_DIR,
						$tpl . $this::TEMPLATES_EXTENSION
					]
				)
		);

		$this->source = $this->readFile();

		if ($minify) {
			$this->minify($this->source);
		}
	}

	/**
	 * Magic Call method that acts as a chained setter only
	 *
	 * @param  string $key  $property to set
	 * @param  array  $args $value(s) to set it to
	 * @return self
	 * @example $template->$prop1($val1, $val2)->$prop2(null);
	 */
	public function __call($key, array $args = array())
	{
		$this->__set($key, join(null, $args));
		return $this;
	}

	/**
	 * Deprecated output method kept for legacy reasons
	 *
	 * @param  bool $print   True to print, false to return
	 * @return mixed         string if $print is false, otherwise self
	 * @deprecated
	 */
	public function out($print = false)
	{
		if ($print) {
			echo "{$this}";
			return $this;
		} else {
			return "{$this}";
		}

	}

	/**
	 * Private method to remove all tabs and newlines from source
	 * Also strips out HTML comments but leaves conditional statements
	 * such as <!--[if IE 6]>Conditional content<![endif]-->
	 *
	 * @param string $string Pointer to string to minify
	 * @return self
	 * @example $this->minify()
	 */
	private function minify(&$string = null)
	{
		$string = str_replace(["\r", "\n", "\t"], null, $string);
		$string = preg_replace($this::MINIFY_EXPRESSION, null, $string);
		return $this;
	}

	/**
	 * Private method for converting array keys when making replacements
	 *
	 * @param string $key array key in replacements array
	 */
	private function replacementsMap($key)
	{
		return $this::PREFIX . strtoupper($key) . $this::SUFFIX;
	}

	/**
	 * Magic method to call when class is used a string
	 *
	 * @return string Modified content of template file
	 * @example echo $template
	 */
	public function __toString()
	{
		$mod = str_replace(
			array_map(
				[$this, 'replacementsMap'],
				array_keys($this->{$this::MAGIC_PROPERTY})
			),
			array_values($this->{$this::MAGIC_PROPERTY}),
			$this->source
		);

		$this->{$this::MAGIC_PROPERTY} = [];
		return $mod;
	}
}
?>
