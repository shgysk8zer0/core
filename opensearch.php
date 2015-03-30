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
 * Creates an XML Document for a search plugin
 * @see https://developer.mozilla.org/en-US/Add-ons/Creating_OpenSearch_plugins_for_Firefox
 */
final class OpenSearch extends \DOMDocument //\shgysk8zer0\Core_API\Abstracts\XML_Document
{
	use API\Traits\Filters;
	use API\Traits\XML_Exception;
	use API\Traits\Path_Info;
	use API\Traits\URL;

	//const CONTENT_TYPE = 'application/opensearchdescription+xml';
	const CONTENT_TYPE = 'application/xml';
	const VERSION = '1.0';
	const ENCODING = 'UTF-8';
	const XMLNS = 'http://www.w3.org/2000/xmlns/';
	const OPENSEARCH_NS = 'http://a9.com/-/spec/opensearch/1.1/';
	const MOZ_XMLNS = 'http://www.mozilla.org/2006/browser/search/';
	const MOZ_NS = 'xmlns:moz';
	const ROOT_EL = 'OpenSearchDescription';
	const ICON = 'favicon.ico';
	//const ICON_MIME = 'image/x-icon';
	const SUGGESTIONS_TYPE = 'application/x-suggestions+json';
	const ERROR_LEVEL = E_ALL;

	/**
	 * A short name for the search engine.
	 * @var string
	 */
	public $ShortName = '';

	/**
	 * A brief description of the search engine.
	 * @var string
	 */
	public $Description = '';

	/**
	 * [$URL description]
	 * @var string
	 */
	public $URL = '';


	public $suggestions_URL = '';


	public $upadte_URL = '';
	/**
	 * [$Image description]
	 * @var string
	 */
	public $Image = '';

	/**
	 * [$UpdateInterval description]
	 * @var integer
	 */
	public $UpdateInterval = 7;

	/**
	 * [$method description]
	 * @var string
	 */
	public $method = 'GET';


	/**
	 * [$template description]
	 * @var string
	 */
	public $template = '';

	/**
	 * [$image_x description]
	 * @var integer
	 */
	private $image_x = 0;

	/**
	 * [$image_y description]
	 * @var integer
	 */
	private $image_y = 0;

	/**
	 * [$image_type description]
	 * @var string
	 */
	private $image_type = '';

	/**
	 * [$body description]
	 * @var \DOMElement
	 */
	private $body;

	public function __construct(
		$name = '',
		$description = '',
		$image = self::ICON,
		$url = ''
	)
	{
		error_reporting(self::ERROR_LEVEL);
		parent::__construct(self::VERSION, self::ENCODING);


		$this->body = $this->appendChild(
			$this->createElementNS(self::OPENSEARCH_NS, self::ROOT_EL)
		);
		$this->getPathInfo($image);
		if (! @file_exists($this->absolute_path)) {
			$this->getPathInfo(self::ICON);
		}
		$this->absolute_path = preg_replace(
			'/^' . preg_quote(
				str_replace(
					DIRECTORY_SEPARATOR,
					'/',
					$_SERVER['DOCUMENT_ROOT']
				)
			, '/') . '/',
			null,
			$this->absolute_path
		);

		$this->URL = $this->parseURL($url)->URLToString(['scheme', 'host']);

		list(
			$a,
			$b,
			$c,
			$this->image_x,
			$this->image_y,
			$this->image_type
		) = array_values(getimagesize($image));

		unset($a, $b, $c);

		if ($this->image_type === 'image/vnd.microsoft.icon') {
			$this->image_type = 'image/x-icon';
		}

		$this->ShortName = $name;
		$this->Description = $description;
		$this->Image = "{$this->URLToString([
			'scheme',
			'host'
		])}/{$this->absolute_path}";
	}

	public function __toString()
	{
		try {
			$this->body->setAttributeNS(
				self::XMLNS,
				self::MOZ_NS,
				self::MOZ_XMLNS
			);
			$this->body->appendChild(
				$this->createElement('ShortName', $this->ShortName)
			);
			$this->body->appendChild(
				$this->createElement('Description', $this->Description)
			);
			$url = $this->body->appendChild(
				$this->createElement('Url', $this->URL)
			);
			$url->setAttribute('type', 'text/html');
			$url->setAttribute('method', $this->method);
			$url->setAttribute(
				'template',
				"$this->URL/tags/{searchTerms}"
			);
			unset($url);

			if (
				is_string($this->suggestions_URL)
				and $this->isURL($this->suggestions_URL)
			) {
				$suggestions = $this->body->appendChild(
					$this->createElement('Url')
				);
				$suggestions->setAttribute('type', self::SUGGESTIONS_TYPE);
				$suggestions->setAttribute('template', $this->suggestions_URL);
			}

			$image = $this->body->appendChild(
				$this->createElement('Image', $this->Image)
			);

			$image->setAttribute('width', $this->image_x);
			$image->setAttribute('height', $this->image_y);
			$image->setAttribute('type', $this->image_type);
			unset($image);

			$this->body->appendChild(
				$this->createElement('InputEncoding', self::ENCODING)
			);
			$this->body->appendChild(
				$this->createElement('moz:SearchForm', $this->URL)
			);
			$this->body->appendChild(
				$this->createElement(
					'moz:UpdateUrl',
					$this->URLToString()
				)
			);
			$this->body->appendChild(
				$this->createElement('moz:IconUpdateUrl', $this->Image)
			);
			$this->body->appendChild(
				$this->createElement(
					'moz:UpdateInterval',
					$this->UpdateInterval
				)
			);
		} catch (\DOMException $e) {
			$this->ExceptionAsXML($e, $this->body);
		}

		return $this->saveXML();
	}
}
