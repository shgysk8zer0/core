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

	namespace \core;
	class xml_api_call extends \core\resources\xml_document {
		private $headers =[],
				$url,
				$urn,
				$charset,
				$verbose,
				$body;

		/**
		 * Constructor for class. Creates new instance of xml_api_call
		 *
		 * Contructs parent (DOMDocument), creates root element
		 * from $call complete with xmlns, sets private vars and
		 * builds headers array
		 *
		 * @param  boolean $verbose     [CURLOPT_VERBOSE]
		 * @param  string  $urn         [Namespace]
		 * @param  string  $charset     [character encoding]
		 */

		public function __construct(
			$verbose = false,
			$charset = null
		) {
			$this->charset = (is_null($charset)) ? ini_get("default_charset") : $charset;
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
			foreach($arguments as $value) {
				$this->set($this->body, $value, $name);
			}
			return $this;
		}

		/**
		 * Append $parent with an element ($tag) with content ($value)
		 *
		 * @param DOMElement $parent
		 * @param  mixed $value [node content]
		 * @param  string $tag  [node name]
		 */

		private function set(DOMNode &$parent, $value, $tag = null) {
			if(is_int($value)) $value = (string)$value;
			elseif(is_object($value)) $value = get_object_vars($value);

			if(is_array($value)) {
				if(is_string($tag)) {
					if(is_assoc($value)) {
						$node = $this->createElement($tag);
					}
					foreach($value as $key => $val) {
						if(is_string($key)) {
							$this->set($node, $val, $key);
						}
						else {
							$this->set($parent, $val, $tag);
						}
					}
					if(isset($node)) {
						$parent->appendChild($node);
					}
				}
				else {
					foreach($value as $key => $val) {
						if(is_string($key)) {
							$this->set($parent, $val, $key);
						}
						else {
							$this->set($parent, $val);
						}
					}
				}
			}
			elseif(is_string($value)) {
				if(is_string($tag)) {
					$parent->appendChild(
						$this->createElement($tag, $value)
					);
				}
				else {
					$parent->appendChild(
						$this->createTextNode($value)
					);
				}
			}
		}

		/**
		 * Private method for setting attributes on $node
		 *
		 * @param  DOMNode       $node       [Node to be setting attributes for]
		 * @param  array         $attributes [key => value array of attributes]
		 */

		private function setAttributes(DOMNode &$node, array $attributes) {
			foreach($attributes as $prop => $value) {
				$attr = $this->createAttribute($prop);
				$attr->value = $value;
				$node->appendChild($attr);
			}
		}

		/**
		 * Sets headers for API call
		 * @param array $headers [$key => $value set of headers]
		 */

		public function set_headers(array $headers) {
			foreach($headers as $key => $value) {
				$this->headers[] => "{$key}: {$value}";
			}
			return $this;
		}

		/**
		 * Create cURL request and return response object
		 *
		 * @param void
		 * @return SimpleXMLElement
		 */

		public function send() {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->saveXML());
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			if($this->verbose) curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			$ch_result = curl_exec($ch);
			curl_close($ch);
			return simplexml_load_string($ch_result);
		}
	}
?>
