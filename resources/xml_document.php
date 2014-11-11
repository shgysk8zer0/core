<?php

	/**
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
	* @version 2014-11-10
	* @package core
	*/

	namespace \core\resources;
	abstract class xml_document extends \DOMDocument {

	private $charset;

	public function __construct($charset = null) {
		$this->charset = (is_null($charset)) ? ini_get('default_charset') : $charset;
		parent::__construct('1.0', $this->charset);
	}

	private function set_attributes(DOMNode $node, array $attributes) {
		foreach($attributes as $prop => $value) {
			$attr = $this->createAttribute($prop);
			$attr->value = str_replace('"', '&quote', $value);
			$node->appendChild($attr);
		}
		return $node;
	}

	private function encode($str) {
		return htmlentities($str, ENT_XML1, $this->charset);
	}
}
?>
