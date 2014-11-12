<?php
	/**
	 * @author Chris Zuber
	 * @package core
	 * @version 2014-11-11
	 * @link http://php.net/manual/en/class.domdocument.php
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

	namespace core\resources;
	class XML_Document extends \DOMDocument {
		protected $charset;

		public function __construct($charset = null) {
			$this->charset = (empty($charset) or !is_string($charset)) ? ini_get('default_charset') : $charset;
			parent::__construct('1.0', $this->charset);
		}

		public function out($filename = null) {
			if(is_null($filename)) {
				return $this->saveXML();
			}
			else {
				$this->formatOutput = true;
				$this->save($filename);
				$this->formatOutput = false;
				return $this;
			}
		}

		private function set_attributes(\DOMElement &$node, array $attributes) {
			foreach($attributes as $prop => $value) {
				$this->set_attribute($node, $prop, $value);
			}
			return $node;
		}

		private function set_attribute(\DOMElement &$node, $prop, $value) {
			$attr = $this->createAttribute($prop);
			$attr->value = str_replace('"', '&quote', $value);
			$node->appendChild($attr);
			return $node;
		}

		private function encode($str) {
			return htmlentities($str, ENT_XML1, $this->charset);
		}

	}
?>
