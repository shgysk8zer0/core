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

	namespace shgysk8zer0\core\resources;
	class XML_Document extends \DOMDocument {
		protected $charset;

		public function __construct($charset = null) {
			$this->charset = (empty($charset) or !is_string($charset)) ? ini_get('default_charset') : $charset;
			parent::__construct('1.0', $this->charset);
		}

		/**
		* Magic setter method
		*
		* Creates a node($key) with content $value
		*
		* @param  string $key   [description]
		* @param  mixed $value [description]
		*/

		public function __set($key, $value) {
			$this->set($this->body, $value, $key);
		}

		/**
		* Chained setter. Appends to $body
		*
		* @param  string $name      [name of element]
		* @param  array  $arguments [array of values]
		* @return ebay_call
		*/

		public function __call($name, array $arguments) {
			list($content, $attributes, $namespace) = array_pad($arguments, 4, null);
			if(is_null($attributes)) $attributes = [];
			$node = new XML_Node($name, null, $namespace);
			$this->body->appendChild($node);
			$this->set($node, $content);
			foreach($attributes as $prop => $value) {
				$node->setAttribute($prop, $value);
			}
			return $this;
		}

		/**
		* Append $parent with an element ($tag) with content ($value)
		*
		* @param \DOMElement $parent
		* @param  mixed $value [node content]
		* @param  string $tag  [node name]
		*/

		private function set(\DOMNode &$parent, $value, $tag = null) {
			if(is_string($tag)) {
				$tmp = $parent;
				$parent = $parent->appendChild($this->createElement(trim($tag)));
			}
			if(is_string($value) or is_numeric($value)) {
				$this->trim($value);
				$parent->appendChild($this->createTextNode($value));
			}
			elseif(is_array($value)) {
				array_map(function($val, $tag) use (&$parent) {
					$this->set($parent, $val, $tag);
				}, array_values($value), array_keys($value));
			}
			elseif(is_object($value) and in_array(get_class($value), [
				'DOMElement',
				'DOMNode',
				'DOMAttr',
				'core\resources\XML_Node'
			])) {
				$parent->appendChild($value);
			}
			elseif(is_object($value)) {
				$this->set($parent, get_object_vars($value));
			}
			if(isset($tmp)) $parent = $tmp;
		}

		public function out($filename = null) {
			if(is_null($filename)) {
				return $this->saveXML();
			}
			else {
				if($this->formatOutput) {
					$this->save($filename);
				}
				else {
					$this->formatOutput = true;
					$this->save($filename);
					$this->formatOutput = false;
				}
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
			$node->setAttribute($prop, $value);
			return $node;
		}

		public function create_attributes(array $attributes) {
			return array_values(array_map(
				[$this, 'attribute'],
				array_keys($attributes),
				array_values($attributes)
			));
		}

		public function encode($str) {
			return htmlentities($str, ENT_XML1, $this->charset);
		}

		public function trim(&$content) {
			if(is_string($content) or is_numeric($content)) {
				$content = str_replace(["\r", "\r\n", "\n", "\t"], null, trim("{$content}"));
				$content = $this->encode($content);
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

		public function attribute($name, $value) {
			return new \DOMAttr($name, $value);
		}

	}
?>
