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
final class OpenSearch extends API\Abstracts\XML_Document implements API\Interfaces\toString
{
	use API\Traits\Filters;
	use API\Traits\XML_Exception;
	use API\Traits\Path_Info;
	use API\Traits\URL;

	const CONTENT_TYPE = 'application/opensearchdescription+xml';
	const VERSION = '1.0';
	const ENCODING = 'UTF-8';
	const XMLNS = 'http://www.w3.org/2000/xmlns/';
	const OPENSEARCH_NS = 'http://a9.com/-/spec/opensearch/1.1/';
	const MOZ_XMLNS = 'http://www.mozilla.org/2006/browser/search/';
	const MOZ_NS = 'xmlns:moz';
	const ROOT_EL = 'OpenSearchDescription';
	const ICON = 'favicon.ico';
	const SUGGESTIONS_TYPE = 'application/x-suggestions+json';
	const ERROR_LEVEL = E_ALL;

	/**
	 * A short name for the search engine.
	 * @var string
	 */
	public $name = '';

	/**
	 * A brief description of the search engine.
	 * @var string
	 */
	public $description = '';

	/**
	 * URL to submit the search terms to
	 * @var string
	 */
	public $URL = '';


	public $suggestions_URL = '';

	/**
	 * URL from which to update the plugin
	 * @var string
	 */
	public $upadte_URL = '';

	/**
	 * Relative path to icon
	 * @var string
	 */
	public $image = '';

	/**
	 * Update Interval? Not sure, but some unit of time
	 * @var int
	 */
	public $UpdateInterval = 7;

	/**
	 * POST or GET
	 * @var string
	 */
	public $method = 'GET';


	/**
	 * Query string to use to submit search
	 * @var string
	 */
	public $template = '?q={searchTerms}';

	/**
	 * Icon width
	 * @var int
	 */
	private $image_x = 0;

	/**
	 * Icon height
	 * @var int
	 */
	private $image_y = 0;

	/**
	 * Icon MIME-Type
	 * @var string
	 */
	private $image_type = '';

	/**
	 * Creates a new OpenSearch instance
	 *
	 * @param string $name        Name of the search plugin
	 * @param string $description Short description
	 * @param [type] $image       Icon to use
	 * @param string $url         URL to submit queries to
	 */
	public function __construct(
		$name = '',
		$description = '',
		$image = self::ICON,
		$url = ''
	)
	{
		parent::__construct(
			self::VERSION,
			self::ENCODING,
			self::ROOT_EL,
			self::OPENSEARCH_NS
		);

		$this->getPathInfo($image);

		if (! @file_exists($this->absolute_path)) {
			$this->getPathInfo(self::ICON);
		}
		$this->absolute_path = '/' . preg_replace(
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

		$this->name = $name;
		$this->description = $description;
		$this->image = $this->URLToString(['scheme','host'])
		. $this->absolute_path;
	}

	/**
	 * Builds the XML document and returns it as a string
	 *
	 * @return string XML Document as string
	 * @example exit($this)
	 */
	public function __toString()
	{
		try {
			$this->root->setAttributeNS(
				self::XMLNS,
				self::MOZ_NS,
				self::MOZ_XMLNS
			);
			$this->ShortName($this->name)
				->searchTitle($this->name)
				->Description($this->description)
				->Url([
					$this->URL,
					'@type' => 'text/html',
					'@method' => $this->method,
					'@template' => "{$this->URL}/{$this->template}"
				])
				->pluginURL("$this->URL/tags/{searchTerms}")
				->Image([
					$this->image,
					'@height' => $this->image_y,
					'@width' => $this->image_x
				])
				->{'moz:SearchForm'}($this->URL)
				->{'moz:UpdateUrl'}($this->URLToString())
				->{'moz:IconUpdateUrl'}($this->image)
				->{'moz:UpdateInterval'}($this->UpdateInterval)
				->Url([
					'@type' => self::CONTENT_TYPE,
					'@rel' => 'self',
					'@template' => $this->URLToString()
				]);

				if (
					is_string($this->suggestions_URL)
					and $this->isURL($this->suggestions_URL)
				) {
					$this->Url([
						'@type' => self::SUGGESTIONS_TYPE,
						'@template' => $this->suggestions_URL
					]);
				}
		} catch (\DOMException $e) {
			$this->ExceptionAsXML($e, $this->root);
		}

		return parent::__toString();
	}
}
