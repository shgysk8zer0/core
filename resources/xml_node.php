<?php
	/**
	 * @author Chris Zuber
	 * @package core
	 * @link http://php.net/manual/en/class.domelement.php
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
	use core\resources as resources;

	class XML_Node extends \DOMElement {
		public function __construct($name, $value = null, $namespaceURI = null) {
			parent::__construct($name, $value, $namespaceURI);
		}

		public function __set($attribute, $value) {
			$this->setAttribute($attribute, $value);
		}

		public function __get($attribute) {
			return $this->getAttribute($attribute);
		}

		public function __call($key, array $args) {
			$this->setAttribute($key, join(',', $args));
			return $this;
		}

		public function __isset($attribute) {
			return $this->hasAttribute($attribute);
		}

		public function __unset($attribute) {
			return $this->removeAttribute($attribute);
		}

		public function value($value) {
			$this->nodeValue = $this->encode($value);
		}

		public function append(XML_Node $node) {
			$this->appendChild($node);
			return $this;
		}

		private function encode($str) {
			return htmlentities((string)$str, ENT_XML1, $this->charset);
		}
	}
?>
