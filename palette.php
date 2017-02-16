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
	 * Encoding
	 * @var string
	 */
	const CHARSET = 'UTF-8';

	/**
	 * XML Version
	 * @var string
	 */
	const VERSION = '1.0';


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
	 * @param String $encoding     Encoding of the document
	 */
	public function __construct(
		String $palette_file = null,
		String $encoding     = self::CHARSET
	)
	{
		$this->setFlags(self::ARRAY_AS_PROPS);
		if (isset($palette_file)) {
			$this->loadFromXML($palette_file, $encoding);
		}
	}

	/**
	 * Loads an XML file and imports `<color>`s
	 * @param  String $palette_file XML file to import from
	 * @param String  $encoding     Encoding of the document
	 * @return self                 Make chainable
	 */
	public function loadFromXML(
		String $palette_file,
		String $encoding     = self::CHARSET
	): self
	{
		if (! pathinfo($palette_file, PATHINFO_EXTENSION)) {
			$palette_file .= self::EXT;
		}

		$xml = new \DOMDocument(self::VERSION, self::CHARSET);
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

	/**
	 * Returns the palette as an XML formatted string
	 * @return string `<?xml version="1.0" encoding="UTF-8"?>...`
	 */
	public function __toString(): String
	{
		return $this->asDOMDocument()->saveXML();
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
	 * @param  String $file      Filename to save as
	 * @param  String $name      Optional name attribute to give the `<palette>`
	 * @param  String  $encoding Encoding of the document
	 * @return Bool              Whether or not it was save successfully
	 */
	public function save(
		String $file,
		String $name     = null,
		String $encoding = self::CHARSET
	): Bool
	{
		if (! pathinfo($file, PATHINFO_EXTENSION)) {
			$file .= self::EXT;
		}
		return $this->_buildXML($name, $encoding)->save($file) !== false;
	}

	/**
	 * Creates a GnomeBuilder compatible DOM object
	 * @param  String $name                Optional name attribute to give the `<palette>`
	 * @param  String $encoding            Encoding of the document
	 * @param  Bool   $format_output       Format output with indentation and extra space
	 * @param  Bool   $preserve_whitespace Do not remove redundant white space
	 * @return DOMDocument                 `<palette><color name="..." value="...">...`
	 */
	public function asDOMDocument(
		String $name                = null,
		String $encoding            = self::CHARSET,
		Bool   $format_output       = true,
		Bool   $preserve_whitespace = true
	): \DOMDocument
	{
		$dom = new \DOMDocument(self::VERSION, $encoding);
		$dom->formatOutput = $format_output;
		$dom->preserveWhitespace = $preserve_whitespace;

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
