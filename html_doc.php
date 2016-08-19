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
namespace shgysk8zer0\Core;

use \shgysk8zer0\Core_API as API;

/**
 * Extends DOMDocument with magic methods (mostly from traits)
 */
class HTML_Doc extends \DOMDocument implements API\Interfaces\Magic_Methods, API\Interfaces\toString
{
	use API\Traits\Magic\DOMDocument;
	use API\Traits\Magic\DOMDoc_Invoke;
	use API\Traits\Magic\Call;
	use API\Traits\Magic\HTML_String;

	const CHARSET        = 'UTF-8';
	const DOM_VERSION    = '1.0';
	const DOCTYPE_FORMAT = '<!doctype %s>';
	const HTML_DOCTYPE   = 'html';

	/**
	* Whether or not to print document on exit/unset
	* @var bool
	*/
	protected $_echo_on_destruct = false;

	/**
	* The <head> of the document
	* @var \DOMElement
	*/
	public $head;

	/**
	* The <body> of the document
	* @var \DOMElement
	*/
	public $body;


	/**
	 * Creates a new DOMDocument with <!doctype>, <html>, <head>, & <body>
	 *
	 * @param string $doctype Doctype for document
	 */
	public function __construct($doctype = self::HTML_DOCTYPE, $echo_on_destruct = false)
	{
		parent::__construct(self::DOM_VERSION, self::CHARSET);
		$this->loadHTML(sprintf(self::DOCTYPE_FORMAT, $doctype));
		$this->registerNodeClass('\\DOMElement', '\\' . __NAMESPACE__ . '\\' . 'HTML_EL');
		$html = $this->appendChild($this->createElement('html'));
		$this->head = $html->appendChild($this->createElement('head'));
		$this->body = $html->appendChild($this->createElement('body'));
		if (is_bool($echo_on_destruct)) {
			$this->_echo_on_destruct = $echo_on_destruct;
		}
	}

	/**
	 * If class is destroyed/unset without having sent headers, print out the document
	 *
	 * @param void
	 * @return void
	 */
	final public function __destruct()
	{
		if ($this->_echo_on_destruct and ! headers_sent()) {
			echo $this;
		}
	}
}
