<?php
	namespace \shgysk8zer0\Core\Resources;

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
	* @version 0.9.0
	* @package shgysk8zer0\Core
	*/
	abstract class DOM_Node extends \DOMDocument
	{

		private $node, $attributes, $content, $charset;
		public function __construct(
			$tag_name, $content,
			array $attributes = null,
			$charset = null
		)
		{
			$this->tag_name = preg_replace('/[\W]/', null, $tag_name);
			$this->encoding = $encoding;
			$this->attributes = $attributes;
			$this->charset = (is_null($charset)) ? ini_get("default_charset") : $charset;
			parent::__construct('1.0', $this->charset);
			$this->node = $this->createElement($tag_name, $this->encode($content));
			$this->set_attributes();
		}

		private function set_attributes()
		{
			if (is_array($this->attributes)) {
				foreach($attributes as $prop => $value) {
					$attr = $this->createAttribute($prop);
					$attr->value = str_replace('"', '&quote', $value);
					$this->node->appendChild($attr);
				}
			}
		}

		private function encode($str)
		{
			return htmlentities($str, ENT_XML1, $this->charset);
		}
	}
