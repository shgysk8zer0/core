<?php

namespace shgysk8zer0\Core;

use \shgysk8zer0\Core_API as API;

final class Creative_Commons_License implements API\Interfaces\String
{
	public $url = 'https://localhost/url';

	public $title = 'TITLLE';

	public $author_url = 'htps://localhost/author_url';

	const TEMPLATE = '<a rel="license" href="http://creativecommons.org/licenses/by/4.0/">
		<img alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by/4.0/88x31.png" />
	</a>
	<br />
	<span xmlns:dct="http://purl.org/dc/terms/" property="dct:title">TITLE</span>
	by <a xmlns:cc="http://creativecommons.org/ns#" href="http://WORK_URL" property="cc:attributionName" rel="cc:attributionURL">
		WORK_NAME
	</a> is licensed under a
	<a rel="license" href="http://creativecommons.org/licenses/by/4.0/">
	Creative Commons Attribution 4.0 International License</a>.
	<br />
	Based on a work at
	<a xmlns:dct="http://purl.org/dc/terms/" href="http://SOURCE_URL" rel="dct:source">
		http://SOURCE_URL
	</a>.
	<br />
	Permissions beyond the scope of this license may be available at
	<a xmlns:cc="http://creativecommons.org/ns#" href="http://PERMISSIONS_URL" rel="cc:morePermissions">
		http://PERMISSIONS_URL
	</a>.';

	const USED = '<details>
		<summary>
			<svg><use xlink:href="images/icons/combined.svg#CreativeCommons"></use></svg>
		</summary>
		<div>
			<span xmlns:dct="http://purl.org/dc/terms/" property="dct:title">
				Hello World
			</span> by
			<a xmlns:cc="http://creativecommons.org/ns#" href="https://plus.google.com/+ChrisZuber?rel=author" property="cc:attributionName" rel="cc:attributionURL author" itemprop="author">
				Chris Zuber
			</a>
			is licensed under a
			<a rel="license" itemprop="license" href="http://creativecommons.org/licenses/by-sa/4.0/">
				Creative Commons Attribution-ShareAlike 4.0 International License
			</a>.
			<time datetime="1399184356" itemprop="datePublished">05/04/2014</time>
		</div>
	</details>';
	const VERSION = '1.1';
	const ENCODING = 'UTF-8';
	const CC_IMAGE = 'https://i.creativecommons.org/l/by/4.0/88x31.png';
	const SVG_NS = 'http://www.w3.org/2000/svg';
	const XLINK_NS = 'http://www.w3.org/1999/xlink';
	const CC_NS = 'http://creativecommons.org/ns#';
	const DCT_NS = 'http://purl.org/dc/terms/';
	const SVG_USE = 'images/icons/combined.svg#CreativeCommons';

	public function __toString()
	{
		try{
			$dom = new \DOMDocument('1.0', self::ENCODING);
			$dom->strictErrorChecking = false;
			$dom->formatOutput = true;
			$dom->preserveWhiteSpace = true;
			$details = $dom->appendChild($dom->createElement('details'));
			$summary = $details->appendChild($dom->createElement('summary'));
			$svg = $summary->appendChild($dom->createElementNS(self::SVG_NS, 'svg'));
			$svg->setAttribute('xmlns:xlink', self::XLINK_NS);
			$svg->setAttribute('version', self::VERSION);
			$use = $svg->appendChild($dom->createElement('use'));
			$use->setAttribute('xlink:href', self::SVG_USE);
			unset($summary, $svg, $use);
			$div = $details->appendChild($dom->createElement('div'));
			$title = $div->appendChild($dom->createElement('span', $this->title));
			$title->setAttribute('xmlns:dct', self::DCT_NS);
			$title->setAttribute('property', 'dct:title');

			return $dom->saveHTML($details);
		} catch (\Exception $e) {
			return "$e";
		}
	}
}
