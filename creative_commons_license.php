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
 * Creative Commons License generator
 * @uses \DOMDocument
 * @uses \DOMElement
 * @uses \DateTime
 * @see https://creativecommons.org/choose/
 */
final class Creative_Commons_License implements API\Interfaces\String
{
	const VERSION              = '1.1';
	const SVG_NS               = 'http://www.w3.org/2000/svg';
	const XLINK_NS             = 'http://www.w3.org/1999/xlink';
	const ENCODING             = 'UTF-8';
	const CC_ALT               = 'Creative Commons License';
	const CC_WIDTH             = 88;
	const CC_HEIGHT            = 33;
	const CC_BORDER            = 0;
	const CC_NS                = 'http://creativecommons.org/ns#';
	const DCT_NS               = 'http://purl.org/dc/terms/';
	const SVG_USE              = 'images/icons/combined.svg#CreativeCommons';
	const ERROR_CHECKING       = true;
	const FORMAT_OUTPUT        = true;
	const PRESERVE_WHITETSPACE = true;
	const CC_BASE_URL          = 'https://creativecommons.org/licenses/';
	const CC_CDN               = 'https://i.creativecommons.org/l/';
	const CC_IMG               = '/88x31.png';
	const CC_VERSION           = '4.0';
	const TIME_FORMAT          = 'l, F jS Y h:i A';

	/**
	 * Title of work
	 * @var string
	 */
	public $title = null;

	/**
	 * Attribute work to name
	 * @var string
	 */
	public $author = null;

	/**
	 * Attribute work to URL
	 * @var string
	 */
	public $author_url = null;

	/**
	 * Source work URL
	 * @var string
	 */
	public $source_url = null;

	/**
	 * More permissions URL
	 * @var string
	 */
	public $permissions_url = null;

	/**
	 * Time work was created (timestamp or formatted date)
	 * @var mixed
	 */
	public $time = 0;

	/**
	 * Allow adaptations of your work to be shared?
	 * @var bool
	 */
	public $allow_adaptation = true;

	/**
	 * If adaptation is allowed, require "share alike"
	 * @var bool
	 */
	public $share_alike = false;

	/**
	 * Allow commercial uses of your work?
	 * @var bool
	 */
	public $allow_commercial_use = true;

	/**
	 * Switch over to SVG image (requires SVG use library to be self-hosted).
	 * These images are not included and require SVG's <use>
	 * @var bool
	 */
	public $use_svg = false;

	/**
	 * Array of all supported licenses with name and URL (segments)
	 * @var array
	 */
	private $_licenses = array(
		'Attribution'                             => 'by/',
		'Attribution-NoDerivatives'               => 'by-nd/',
		'Attribution-ShareAlike'                  => 'by-sa/',
		'Attribution-NonCommercial'               => 'by-nc/',
		'Attribution-NonCommercial-NoDerivatives' => 'by-nc-nd/',
		'Attribution-NonCommercial-ShareAlike'    => 'by-nc-sa/'
	);

	/**
	 * Dynamically build HTML string from class properties
	 *
	 * @param void
	 * @return string HTML formatted license
	 */
	public function __toString()
	{
		// Build license name and key for $_licenses array
		$type = 'Attribution';
		if (! $this->allow_commercial_use) {
			$type .= '-NonCommercial';
		}
		if (! $this->allow_adaptation) {
			$type .= '-NoDerivatives';
		} elseif ($this->allow_adaptation and $this->share_alike) {
			$type .= '-ShareAlike';
		}

		// Create time from either formated date or int datetime
		if (is_numeric($this->time)) {
			$this->time = date(self::TIME_FORMAT, $this->time);
		} elseif (is_string($this->time)) {
			$this->time = date(self::TIME_FORMAT, strtotime($this->time));
		}

		try{
			// Cerate DOMDocument and set options
			$dom = new \DOMDocument('1.0', self::ENCODING);
			$dom->strictErrorChecking = self::ERROR_CHECKING;
			$dom->formatOutput        = self::FORMAT_OUTPUT;
			$dom->preserveWhiteSpace  = self::PRESERVE_WHITETSPACE;

			// Create <details>, <summary>, & <svg>
			$details = $dom->appendChild($dom->createElement('details'));
			$summary = $details->appendChild($dom->createElement('summary'));
			if ($this->use_svg) {
				$svg = $summary->appendChild($dom->createElementNS(self::SVG_NS, 'svg'));
				$svg->setAttribute('xmlns:xlink', self::XLINK_NS);
				$svg->setAttribute('version', self::VERSION);
				$use = $svg->appendChild($dom->createElement('use'));
				$use->setAttribute('xlink:href', self::SVG_USE);
				unset($svg, $use);
			} else {
				$image = $summary->appendChild($dom->createElement('img'));
				$image->setAttribute('alt', self::CC_ALT);
				$image->setAttribute('width', self::CC_WIDTH);
				$image->setAttribute('height', self::CC_HEIGHT);
				$image->setAttribute('border', self::CC_BORDER);
				$image->setAttribute(
					'src',
					self::CC_CDN . $this->_licenses[$type] . self::CC_VERSION . self::CC_IMG
				);
				unset($image);
			}

			unset($summary);

			// $div will serve as a container for the rest
			$div = $details->appendChild($dom->createElement('div'));

			// Create elements and attributes for title
			if (is_string($this->title)) {
				$title = $div->appendChild($dom->createElement('span', $this->title));
				$title->setAttribute('xmlns:dct', self::DCT_NS);
				$title->setAttribute('property', 'dct:title');
			} else {
				$div->appendChild($dom->createTextNode('This work'));
			}

			// Create author info, validating data
			if (is_string($this->author) or filter_var($this->author_url, FILTER_VALIDATE_URL)) {
				$div->appendChild($dom->createTextNode(' by '));

				if (filter_var($this->author_url, FILTER_VALIDATE_URL)) {
					if (is_string($this->author)) {
						// author is set & author_url is a valid URL
						$author = $div->appendChild($dom->createElement('a', $this->author));
					} else {
						// author is not set but author_url is a valid URL
						$author = $div->appendChild($dom->createElement('a', $this->author_url));
					}
					// Set URL attributes on author node
					$author->setAttribute('href', $this->author_url);
					$author->setAttribute('rel', 'cc:attributionURL author');
				} else {
					// Author is set but author_url is not set or is not valid
					$author = $div->appendChild($dom->createElement('span', $this->author));
				}

				// Properties set on any type of author node
				$author->setAttribute('property', 'cc:attributionName');
				$author->setAttribute('itemprop', 'author');
				$author->setAttribute('xmlns:cc', self::CC_NS);

				unset($author);
			}

			$div->appendChild($dom->createTextNode(' is licensed under a '));

			// Create license link and set attributes (will always be set)
			$license = $div->appendChild(
				$dom->createElement(
					'a',
					'Creative Commons ' . $type . ' ' . self::CC_VERSION . ' International License'
				)
			);
			$license->setAttribute('rel', 'license');
			$license->setAttribute('itemprop', 'license');
			$license->setAttribute('href', self::CC_BASE_URL . $this->_licenses[$type] . self::CC_VERSION . '/');
			unset($license);

			// if $source_url is a valid URL, create node & set attributes
			if (filter_var($this->source_url, FILTER_VALIDATE_URL)) {
				$div->appendChild($dom->createElement('br'));
				$div->appendChild($dom->createTextNode('Based on a work at '));
				$source = $div->appendChild($dom->createElement('a', $this->source_url));
				$source->setAttribute('xmlns:dct', self::DCT_NS);
				$source->setAttribute('href', $this->source_url);
				$source->setAttribute('rel', 'dct:source');
				unset($source);
			}

			// if $permissions_url is a valid URL, create node & set attributes
			if (filter_var($this->permissions_url, FILTER_VALIDATE_URL)) {
				$div->appendChild($dom->createElement('br'));
				$div->appendChild($dom->createTextNode('Permissions beyond the scope of this license may be available at '));
				$permissions = $div->appendChild($dom->createElement('a', $this->permissions_url));
				$permissions->setAttribute('href', $this->source_url);
				$permissions->setAttribute('xmlns:cc', self::CC_NS);
				$permissions->setAttribute('rel', 'cc:morePermissions');

				unset($permissions);
			}

			$div->appendChild($dom->createElement('br'));

			// Create <time> and set attributes
			$time = $div->appendChild($dom->createElement('time', $this->time));
			$time->setAttribute('datetime', date(\DateTime::W3C, strtotime($this->time)));
			$time->setAttribute('itemprop', 'datePublished');

			// Return the HTML as a string
			return $dom->saveHTML($details);
		} catch (\Exception $e) {
			return "$e";
		}
	}
}
