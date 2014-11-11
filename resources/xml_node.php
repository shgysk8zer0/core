<?php
	/**
	 * @author Chris Zuber
	 * @package core
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
	use core\resources as resources;

	class XML_Node extends resources\XML_Document {
		protected $tag, $attrs = [], $node = null;
		public $content = null;

		public function __construct(
			$tag,
			array $attributes = null,
			$urn = null,
			$content = null,
			$charset = null
		) {
			$this->tag = preg_replace('/\W/', null, $tag);
			$this->attrs = $attributes;
			$this->content = $content;
			parent::__construct($charset);
		}

		public function __set($prop, $value) {
			$this->set($prop, $value);
		}

		public function __get($prop) {
			if(array_key_exists($prop, $this->attrs)) {
				return $this->attrs[$prop];
			}

			else {
				return null;
			}
		}

		public function __call($prop, array $args) {
			$this->set($prop, joni(',', $args));
			return $this;
		}

		private function set($prop, $value) {
			$this->attrs[$prop] = $value;
			if(isset($this->node)) {
				$this->set_attribute($this->node, $prop, $value);
			}
		}

		public function append_to(XML_Node &$parent) {
			if(is_null($this->urn)) {
				$this->node = $parent->appendChild(
					$this->createElement(
					$this->tag,
						$this->content
					)
				);
			}

			else {
				$this->node = $parent->appendChild(
					$this->createElementNS(
						$this->urn,
						$this->tag,
						$this->content
					)
				);
			}

			if(is_array($this->attributes)) {
				$this->set_attributes($this->node, $this->attrs);
			}

			return $this;
		}
	}
?>
