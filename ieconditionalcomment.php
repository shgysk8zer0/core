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

/**
 * Class to create IE conditional comments for DOMDocuments
 */
final class IEConditionalComment extends \DOMComment
{
	const IF_IE                     = '[if IE]>';
	const IF_NOT_IE                 = '[if ! IE]>';
	const IF_IE_VERSION             = '[if IE %d]>';
	const IF_IE_VERSION_COMPARE     = '[if IE %s %d]>';
	const IF_NOT_IE_VERSION_COMPARE = '[if ! IE %s %d]>';
	const IF_NOT_IE_VERSION         = '[if ! IE %d]>';
	const CLOSE                     = '<![endif]';

	const DEFAULT_VERSION           = null;
	const DEFAULT_OPERATOR          = null;
	const CREATE_AND_APPEND         = false;

	/**
	 * Content of conditional comment
	 * @var string
	 */
	private $content = '';

	/**
	 * Version number to match/compare against
	 * @var mixed
	 */
	private $version = self::DEFAULT_VERSION;

	/**
	 * Less/greater than (or equal to)
	 * @var string
	 */
	private $operator = self::DEFAULT_OPERATOR;

	/**
	 * Use neated IE conditional comment
	 * @var bool
	 */
	private $negate = false;

	/**
	 * Array of valid operators
	 * @var array
	 */
	private $_operators = array('lt, gt, lte, gte');

	/**
	 * Create a new IE conditional comment
	 *
	 * @param int    $version  [description]
	 * @param string $operator [description]
	 * @param bool   $negate   [description]
	 * @param string $content  [description]
	 */
	public function __construct(
		$version    = self::DEFAULT_VERSION,
		$operator   = self::DEFAULT_OPERATOR,
		$negate     = false,
		$content    = null
	)
	{
		parent::__construct();

		if (is_string($content) or $content instanceof \shgysk8zer0\Core_API\Interfaces\toString) {
			$this->content = "$content";
		} elseif ($content instanceof \DOMElement) {
			if (is_null($content->ownerDocument)) {
				(new \DOMDocument)->appendChild($content);
			}
			$this->content = $content->ownerDocument->saveHTML($content);
		}

		if (is_int($version)) {
			$this->version = $version;
		}

		if (is_bool($negate)) {
			$this->negate = $negate;
		}

		if (is_string($operator) and in_array($operator, $this->_operators)) {
			$this->operator = $operator;
		}
		$this->appendData($this->_getCondition() . $this->content);
	}

	/**
	 * Convert the DOMComment into an HTML string
	 *
	 * @param void
	 * @return string <!--if IE...>...<![endif]-->
	 */
	public function __toString()
	{
		if (@is_null($this->ownerDocument)) {
			(new \DOMDocument)->appendChild($this);
		}
		$this->appendData(self::CLOSE);
		return $this->ownerDocument->saveHTML($this);
	}

	/**
	 * Build the conditional portion of the comment
	 *
	 * @param void
	 * @return string [if IE...]>
	 */
	private function _getCondition()
	{
		if (is_int($this->version) and is_string($this->operator) and ! $this->negate) {
			return sprintf(self::IF_IE_VERSION_COMPARE, $this->operator, $this->version);
		} elseif (is_int($this->version) and is_string($this->operator)) {
			return sprintf(self::IF_NOT_IE_VERSION_COMPARE, $this->operator, $this->version);
		} elseif (is_int($this->version) and ! $this->negate) {
			return sprintf(self::IF_IE_VERSION, $this->version);
		} elseif (is_int($this->version) and $this->negate) {
			return sprintf(self::IF_NOT_IE_VERSION, $this->version);
		} elseif($this->negate === true) {
			return self::IF_NOT_IE;
		} else {
			return self::IF_IE;
		}
	}
}
