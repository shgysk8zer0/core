<?php
	/**
	 * @author Chris Zuber
	 * @package core
	 * @version 2014-11-11
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

	namespace core;
	use core\resources as resources;
	class XML_API_Call extends resources\XML_Document {
		private $url,
				$headers = [],
				$urn,
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
		 * @param  string  $url         [URL for cURL]
		 * @param  string  $charset     [character encoding]
		 */

		public function __construct(
			$url,
			array $headers = null,
			$root_tag = 'root',
			$urn = null,
			$charset = null,
			$verbose = false
		) {
			parent::__construct($charset);
			if(isset($headers)) {
				$this->set_headers($headers);
			}
			$this->url = $url;
			$this->urn = $urn;
			$this->verbose = $verbose;
			$this->body = new resources\XML_Node($root_tag, null, $urn);
			$this->appendChild($this->body);
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
		 * Append a child to a parent node
		 * @param  DOMElement $node   [node to append]
		 * @param  string     $parent [tagName of parent element]
		 * @param  integer    $n      [for multiple instances of $parent, which one?]
		 * @return XML_API_Call
		 */

		public function append(\DOMElement $node, $parent = null, $n = 0) {
			if(is_null($parent)) {
				$this->body->append($node);
			}

			else {
				$this->body->getElementsByTagName($parent)->item($n)->appendChild($node);
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

		private function set(resources\XML_Node &$parent, $value, $tag = null) {
			if(is_int($value)) $value = (string)$value;
			elseif(is_object($value)) $value = get_object_vars($value);

			if(is_array($value)) {
				if(is_string($tag)) {
					if(is_assoc($value)) {
						$node = $this->create($tag);
						$parent->appendChild($node);
					}
					foreach($value as $key => $val) {
						if(is_string($key)) {
							$this->set($node, $val, $key);
						}
						else {
							$this->set($parent, $val, $tag);
						}
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
						$this->create($tag, $value)
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
		 * @param  \DOMElement       $node       [Node to be setting attributes for]
		 * @param  array         $attributes [key => value array of attributes]
		 */

		private function setAttributes(resources\XML_Node &$node, array $attributes) {
			foreach($attributes as $prop => $value) {
				$attr = $this->createAttribute($prop);
				$attr->value = $value;
				$node->appendChild($attr);
			}
		}

		/**
		 * Create a new XML_Node
		 *
		 * DOMDocument::createElement would return a DOMElement, which lacks
		 * magic methods. Use this method to use my extended DOMElement class
		 * instead.
		 *
		 * @param  string $name         [tagName for new XML_Node]
		 * @param  string $value        [nodeValue/textContent for created XML_Node]
		 * @param  string $namespaceURI [Namespace URI for created node]
		 * @return XML_Node
		 */

		public function create($name, $value = null, $namespaceURI = null) {
			return new resources\XML_Node($name, $value, $namespaceURI);
		}

		/**
		 * Sets headers for API call
		 * @param array $headers [$key => $value set of headers]
		 * @return XML_API_Call
		 */

		public function set_headers(array $headers) {
			foreach($headers as $key => $value) {
				$this->headers[] = "{$key}: {$value}";
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
