<?php
/**
 * @author Chris Zuber <shgysk8zer0@gmail.com>
 * @package shgysk8zer0\Core
 * @subpackage Elements
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
namespace shgysk8zer0\Core\Elements;

use \shgysk8zer0\Core_API as API;

/**
 * Allows <script> elements to be easily created and manipulated
 */
class Script extends \DOMElement
{
	use API\Traits\Magic\HTML_String;

	/**
	 * Creates a new <script> element with options for src, async, defer, and type
	 *
	 * @param string $src   The source URL
	 * @param bool   $async Whether or not the script is to be asynchronous
	 * @param bool   $defer Whether or not to defer parsing of script
	 * @param string $type  The type attribute, defaults to "application/javascript" without version
	 */
	public function __construct($src, $async = false, $defer = false, $type = 'application/javascript')
	{
		parent::__construct('script');
		(new \DOMDocument('1.0', 'UTF-8'))->appendChild($this);
		$this->setAttribute('src', $src);
		$this->setAttribute('type', $type);
		if ($async) {
			$this->setAttribute('async', 'async');
		}
		if ($defer) {
			$this->setAttribute('defer', 'defer');
		}
	}
}
