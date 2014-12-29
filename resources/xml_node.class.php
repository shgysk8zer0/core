<?php
	/**
	 * Wrapper for DOMElement
	 *
	 * Apply magic methods for setting/getting attributes to make them easier
	 * to work with
	 *
	 * @author Chris Zuber
	 * @package shgysk8zer0\Core
	 * @version 1.0.0
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

	namespace shgysk8zer0\Core\resources;
	use core\resources as resources;

	class XML_Node extends \DOMElement {
		/**
		 * Class constructor | Creates a new DOMElement
		 *
		 * @param string $name         [TagName for new element]
		 * @param string $value        [nodeValue/textContent for new element]
		 * @param string $namespaceURI [Namespace for new element]
		 */

		public function __construct($name, $content = null, $namespaceURI = null) {
			if(is_string($content) or is_numeric($content)) {
				parent::__construct($name, "{$content}", $namespaceURI);
			}
			elseif(isset($content) and is_object($content) and in_array(get_class($content), [
				'DOMElement',
				'DOMNode',
				'core\resources\XML_Node'
			])) {
				parent::__construct($name, null, $namespaceURI);
				$this->append($content);
			}
			else {
				parent::__construct($name, null, $namespaceURI);
			}
		}

		/**
		 * Set an attribute using magic setter method
		 *
		 * @param string $attribute [Property/attribute name]
		 * @param string $value     [Value to set the attribute to]
		 * @return void
		 */

		public function __set($attribute, $value) {
			$this->setAttribute($attribute, $value);
		}

		/**
		 * Get a node's attribute using magic methods
		 *
		 * @param  string $attribute [Property/attribute name]
		 * @return string            [Value to set the attribute to]
		 */

		public function __get($attribute) {
			return $this->getAttribute($attribute);
		}

		/**
		 * Set an attribute to a comma separated list of values
		 *
		 * @param  string $key  [Property/attribute name]
		 * @param  array  $args [array of values to be setting]
		 * @return XML_Node
		 */

		public function __call($key, array $args) {
			$this->setAttribute($key, join(',', $args));
			return $this;
		}

		/**
		 * Check whether or not an XML_Node has an attribute
		 *
		 * @param  string  $attribute [Property/attribute name]
		 * @return boolean            [Whether or not the attribute is set]
		 */

		public function __isset($attribute) {
			return $this->hasAttribute($attribute);
		}

		/**
		 * Remove an attribute from an XML_Node
		 *
		 * @param string $attribute [Property/attribute name]
		 */

		public function __unset($attribute) {
			return $this->removeAttribute($attribute);
		}

		/**
		 * Magic method to set nodeValue/textContent
		 *
		 * Method is called when class is called as a fucntion
		 * @param  string $value [nodeValue/textContent]
		 * @return XML_Node
		 */

		public function __invoke($value) {
			return $this->value($value);
		}

		/**
		 * Set the nodeValue/textContent of an XML_Node
		 *
		 * @param  string $value [nodeValue/textContent]
		 * @return XML_Node
		 */


		public function value($content) {
			if(is_string($content)) {
				$this->nodeValue = $this->trim($content);
			}
			elseif(in_array(get_class($content), [
				'DOMElement',
				'DOMNode',
				'core\resources\XML_Node',
				'core\XML_API_Call'
			])) {
				$node = new self($name);
				$this->body->append($node);
				$node->appendChild($content);
			}

			return $this;
		}

		/**
		 * XML_Node class' appencChild method
		 *
		 * DOMElement's appendChild method returns the appended node, breaking
		 * possible chaining of methods. Return XML_Node instead to maintain it.
		 *
		 * @param  XML_Node $node [Node to be appended]
		 * @return XML_Node         [Parent node/Self]
		 */

		public function append(XML_Node $node) {
			$this->appendChild($node);
			return $this;
		}

		/**
		 * Make a string safe for use in XML
		 * @param  string $str [unencoded string]
		 * @return string      [encoded string]
		 */

		private function encode($str) {
			return htmlentities((string)$str, ENT_XML1, $this->charset);
		}

		protected function trim(&$content) {
			if(is_string($content) or is_numeric($content)) {
				$content = str_replace(["\r", "\r\n", "\n", "\t"], null, trim("{$content}"));
				$content = $this->encode($str);
			}
			elseif(is_array($content)) {
				array_walk($content, [$this, 'trim']);
			}
			elseif(is_object($content)) {
				foreach(get_object_vars($content) as $key => $value) {
					$content->$key = $this->trim($value);
				}
			}

			return $content;
		}

	}
?>
