<?php
/**
 * @author Chris Zuber <shgysk8zer0@gmail.com>
 * @package shgysk8zer0\Core
 * @version 1.0.0
 * @copyright 2017, Chris Zuber
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
namespace shgysk8zer0\Core;

/**
 * Class to use GnomeBuilder compatible palettes in PHP
 * Uses XML with `<color name="..." value="..."/>`
 * but will work with any XML file containing `<color>` elements
 */
final class Palette extends \ArrayObject implements \JSONSerializable
{
	use \shgysk8zer0\Core_API\Traits\Singleton;

	/**
	 * Default extension when loading/saving files
	 * @var string
	 */
	const EXT = '.xml';

	/**
	 * Tag name of root element
	 * @var string
	 */
	const ROOT_EL = 'palette';

	/**
	 * Tag name of color elements
	 * @var string
	 */
	const COLOR_TAG = 'color';

	/**
	 * Attribute name for color names
	 * @var string
	 */
	const NAME_ATTR = 'name';

	/**
	 * Attribute name for color values
	 * @var string
	 */
	const VALUE_ATTR = 'value';


	/**
	 * This is the license used in GnomeBuilder palette exports
	 * @var string
	 */
	const LICENSE = 'Copyright (C) 2016 GNOME Builder Team at irc.gimp.net/#gnome-builder
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>
';

	/**
	 * Create a new Palette instance, optionall from an XML palette file
	 * @param String $palette_file Optional XML file to load from
	 */
	public function __construct(String $palette_file = null)
	{
		parent::__construct([], self::ARRAY_AS_PROPS);
		if (isset($palette_file)) {
			$this->loadFromXML($palette_file);
		}
	}

	/**
	 * Loads an XML file and imports `<color>`s
	 * @param  String $palette_file XML file to import from
	 * @return self                 Make chainable
	 */
	public function loadFromXML(String $palette_file): self
	{
		if (! pathinfo($palette_file, PATHINFO_EXTENSION)) {
			$palette_file .= self::EXT;
		}

		$xml = new \DOMDocument();
		$xml->load($palette_file, LIBXML_NOERROR);

		foreach ($xml->getElementsByTagName(self::COLOR_TAG) as $color) {
			if ($color->hasAttribute(self::NAME_ATTR) and $color->hasAttribute(self::VALUE_ATTR)) {
				$this->{$color->getAttribute(self::NAME_ATTR)} = $color->getAttribute(self::VALUE_ATTR);
			} elseif ($color->hasAttribute(self::VALUE_ATTR)) {
				$this->append($color->getAttribute(self::VALUE_ATTR));
			} elseif ($color->hasAttribute(self::NAME_ATTR)) {
				$this->{$color->getAttribute(self::NAME_ATTR)} = $color->textContent;
			} else {
				$this->append($color->textContent);
			}
		}
		return $this;
	}

	/**
	 * Returns data to use for `json_encode`
	 * @return Array Properties to encode to JSON
	 */
	public function jsonSerialize(): Array
	{
		return $this->getArrayCopy();
	}

	public function __toString(): String
	{
		return $this->_buildXML()->saveXML();
	}

	/**
	 * Returns data to use for debugging functions, such as `var_dump`
	 * @return Array Properties to encode to JSON
	 */
	public function __debugInfo(): Array
	{
		return $this->getArrayCopy();
	}

	/**
	 * Exports Palette to a GnomeBuilder compatible template file
	 * @param  String $file Filename to save as
	 * @param  String $name Optional name attribute to give the `<palette>`
	 * @return Bool         Whether or not it was save successfully
	 */
	public function save(String $file, String $name = null): Bool
	{
		if (! pathinfo($file, PATHINFO_EXTENSION)) {
			$file .= self::EXT;
		}
		return $this->_buildXML($name)->save($file) !== false;
	}

	/**
	 * Creates a GnomeBuilder compatible DOM object
	 * @param  String $name Optional name attribute to give the `<palette>`
	 * @return DOMDocument  The DOMDocument containing the `<palette>` & `<color>`s
	 */
	protected function _buildXML(String $name = null): \DOMDocument
	{
		$dom = new \DOMDocument();
		$dom->formatOutput = true;
		$dom->preserveWhitespace = true;

		$dom->appendChild($dom->createComment(self::LICENSE));
		$dom->appendChild($dom->createElement(self::ROOT_EL));
		if (isset($name)) {
			$dom->documentElement->setAttribute(self::NAME_ATTR, $name);
		}

		foreach ($this as $name => $value) {
			$color = $dom->createElement(self::COLOR_TAG);
			$dom->documentElement->appendChild($color);
			if (is_string($name)) {
				$color->setAttribute(self::NAME_ATTR, $name);
			}

			$color->setAttribute(self::VALUE_ATTR, $value);
		}
		return $dom;
	}
}
