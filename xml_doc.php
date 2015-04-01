<?php
/**
 * @author Chris Zuber
 * @package shgysk8zer0\Core
 * @version 1.0.0
 * @copyright 2015, Chris Zuber
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

use \shgysk8zer0\Core_API as API;

/**
 * The easiest way to create an XML Document
 *
 * Constructor gets all of the information necessary to create an XML Document.
 * Uses traits, giving the class an easy-to-use `__toString` method as well
 * as the `XMLAppend` method, which is used for appending nodes and setting
 * attributes
 */
final class XML_Doc extends \DOMDocument
{
	use API\Traits\Magic\XML_String;
	use API\Traits\XMLAppend;

	const VERSION   = '1.0';
	const ENCODING  = 'UTF-8';
	const ROOT_EL   = 'root';

	/**
	 * Protected root element for the XML Document
	 * @var \DOMElement
	 */
	protected $root = null;

	/**
	 * Creates an entire XML Document with just the constructor
	 *
	 * @param string $root_el    Tag name for root element
	 * @param array  $nodes      Array of nodes (including attributes) to create
	 * @param string $xmlns      XML namespace for root element
	 * @param string $version    XLM version to use
	 * @param string $encoding   Character encoding
	 */
	public function __construct(
		$root_el     = self::ROOT_EL,
		array $nodes = array(),
		$xmlns       = null,
		$version     = self::VERSION,
		$encoding    = self::ENCODING
	)
	{
		// Create the \DOMDocument
		parent::__construct($version, $encoding);

		// Check if $xmlns is a valid URL. Create root element accordingly
		if (filter_var($xmlns, FILTER_VALIDATE_URL)) {
			$this->root = $this->appendChild(
				$this->createElementNS($xmlns, $root_el)
			);
		} else {
			$this->root = $this->appendChild($this->createElement($root_el));
		}

		// Loop through $nodes array, appending all nodes to $root
		foreach ($nodes as $tag => $content) {
			$this->XMLAppend($this->root, [$tag => $content]);
		}
	}
}
