<?php
/**
 * @author Chris Zuber <shgysk8zer0@gmail.com>
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
namespace shgysk8zer0\Core\Elements;

use \shgysk8zer0\Core_API as API;

/**
 * Class for easily creating <dialog> elements
 */
final class Dialog extends \DOMElement implements API\Interfaces\toString
{
	use API\Traits\DOMImportHTML;
	use API\Traits\Magic\HTML_String;

	const TAG       = 'dialog';
	const ID_SUFFIX = '_dialog';

	/**
	 * The ID attribute for the dialog
	 *
	 * @var string
	 */
	public $id = null;

	/**
	 * Creates a new <dialog> element, along with the delete/close button
	 *
	 * @param string $id      The id attribute, used to close/delete & show dialog
	 * @param mixed  $content String or \DOMNode
	 */
	public function __construct($id, $content = null)
	{
		$this->id = "#{$id}" . self::ID_SUFFIX;
		parent::__construct(self::TAG);
		(new \DOMDocument('1.0', 'UTF-8'))->appendChild($this);
		$this->setAttribute('id', $id . self::ID_SUFFIX);
		$this->appendChild(new \DOMElement('button'))->setAttribute('data-delete', $this->id);
		$fullscreen = $this->appendChild(new \DOMElement('button'));
		$fullscreen->setAttribute('data-fullscreen', $this->id);
		$fullscreen->setAttribute('title', 'View full-screen');
		$this->appendChild(new \DOMElement('br'));
		$svg = $fullscreen->appendChild(new \DOMElement('svg'));
		$svg->setAttribute('class', 'currentColor icon');
		$use = $svg->appendChild(new \DOMElement('use'));
		$use->setAttribute('xlink:href', 'images/icons/combined.svg#screen-full');
		if ($content instanceof \DOMNode) {
			$this->appendChild(isset($content->ownerDocument)
				? $this->ownerDocument->importNode($content, true)
				: $content
			);
		} elseif (is_string($content)) {
			$this->importHTML($content);
		}
	}
}
