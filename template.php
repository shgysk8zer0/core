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
 * File contents are loaded and optionaly inified
 * Placeholders are dynamically replaced.
 * When retrieving output, all data is reset and ready
 * to be used again.
 * @var template $instance
 * @var string $path
 * @var string $source
 * @var array $replacements
 * @var string $seperator
 * @var boolean $minify_results
 * @example
 * 	$template = new template('path/to/tempalte', '^', true);
 * 	$template->old = 'New';
 * 	$template->replace = 'Updated';
 * 	$table .= $template->out();
 * 	$table .= $template->old('Newer')->replace('Updated Again')->out();
 */
class Template implements API\Interfaces\Magic_Methods
{
	use API\Traits\Singleton;
	use API\Traits\Magic_Methods;
	use API\Traits\Magic_Call;
	use API\Traits\File_IO;

	private $source = '', $replacements = [], $seperator, $minify_results;
	const MAGIC_PROPERTY = 'replacements';
	const MINIFY_EXPRESSION = '/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/';
	const SEPARATOR = '%';
	const TEMPLATES_EXTENSION = '.tpl';
	const TEMPLATES_DIR = 'templates';

	/**
	 * Reads the template specified by $tpl
	 * Reads the file from BASE . "/components/templates/{$tpl}.tpl"
	 * Will exit if file cannot be read (either DNE or denied by permissions)
	 *
	 * @param string $tpl (Path of template, no extension)
	 * @param string $seperator (Character to mark beginning and end of placeholders)
	 * @param boolean $minify (whether or not to eliminate tabs and newlines)
	 * @return template object/class
	 * @example $template = template::load($template_file, '^', true)
	 * @example $template = new template($template_file)
	 */
	public function __construct($tpl, $seperator = self::SEPARATOR, $minify = true)
	{
		$this->path = (defined('THEME'))
			? BASE . '/components/' . THEME .'/templates/' . (string)$tpl . '.tpl'
			: BASE . '/components/templates/' . (string)$tpl . '.tpl';

		$this->seperator = (string)$seperator;
		$this->minify_results = $minify;
		if (file_exists($this->path)) {
			$this->source = file_get_contents($this->path);
		} else {
			exit("Attempted to load a template that cannot be read. {$tpl} cannot be read");
		}

		if ($this->minify_results) {
			$this->minify($this->source);
		}
	}

	/**
	 * Private method to remove all tabs and newlines from source
	 * Also strips out HTML comments but leaves conditional statements
	 * such as <!--[if IE 6]>Conditional content<![endif]-->
	 *
	 * @param string $string (Pointer to string to minify)
	 * @return self
	 * @example $this->minify()
	 */
	private function minify(&$string = null)
	{
		$string = str_replace(["\r", "\n", "\t"], [], (string)$string);
		$string = preg_replace($this::MINIFY_EXPRESSION, null, $string);
		return $this;
	}

	/**
	 * Private method to prepare replacements
	 *
	 * Adds to the replacements array with a key of $replace
	 * and a value of $with
	 *
	 * @param string $replace (placeholder text in the template)
	 * @param mixed $with (What it is being replace with)
	 * @param string $join (If $with is an array, join() with $join)
	 * @return self
	 * @example $this->replace('old', 'new')
	 */
	private function replace($replace = null, $with = null, $join = null)
	{
		$this->replacements[
				$this->seperator . strtoupper((string)$replace) . $this->seperator
			] = (is_array($with))
				? join($join, $with)
				: $with;

		return $this;
	}

	/**
	 * Private method for replacing all placeholders with their
	 * replacements. Returns the results, but does not update the original
	 *
	 * @param void
	 * @return string
	 * @example $results = $this->get_results()
	 */
	private function get_results()
	{
		return str_replace(
			array_keys($this->replacements),
			array_values($this->replacements),
			$this->source
		);
	}

	/**
	 * Private method to reset the array of replacements
	 *
	 * @param void
	 * @return self
	 * @example $this->clear()
	 */
	private function clear()
	{
		$this->replacements = [];
		return $this;
	}

	/**
	 * Loops through $arr using, replacing array_key with array_value in $template
	 * See __set() documentation for description of template formatting.
	 *
	 * @param array $arr
	 * @return self
	 * @example $template->set([$placeholder => $replacement][, ...])
	 */
	public function set(array $arr)
	{
		foreach($arr as $replace => $with) {
			$this->replace($replace, $with);
		}
		return $this;
	}

	/**
	 * Executes string replacement without updating
	 * the source (original template content).
	 *
	 * Will either return the result (default), or will
	 * echo it (if $print evaluates as true)
	 *
	 * @param boolean $print
	 * @return string or void
	 * @example $conntent = $template->out([false[, true]]);
	 */
	public function out($print = false)
	{
		if ($print) {
			echo $this->get_results();
			return $this->clear();
			return $this;
		} else {
			$result = $this->get_results();
			$this->clear();
			return $result;
		}
	}
}
?>
