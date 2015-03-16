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
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace shgysk8zer0\Core;

use \shgysk8zer0\Core_API as API;
/**
 * PHP based caching
 *
 * Mostyl useful if you lack the ability to set headers via Apache,
 * though it may be useful even if you do (.appcache seems problematic)
 *
 * Only sets headers. No HTML or other output is created
 */
class Magic_Cache implements API\Interfaces\File_IO, API\Interfaces\Path_Info
{
	use API\Traits\File_IO;
	//use API\Traits\File_Info;

	const DATE_FORMAT = 'D, d M Y H:i:s T';

	/**
	 * Mime-type
	 * @var string
	 */
	private $type;

	/**
	 * File size
	 * @var int
	 */
	private $size = 0;

	/**
	 * MD5 of file
	 * @var string
	 */
	private $etag;

	/**
	 * File mod-time (timestamp)
	 * @var int
	 */
	private $mod_time;

	/**
	 * Whether or not it is gzipped
	 * @var bool
	 */
	private $gz = false;

	/**
	 * HTTP Response Code
	 * @var int
	 */
	private $status = 200;

	/**
	 * The only public method of the class.
	 *
	 * It determines which methods to call
	 *
	 * Get the MD5 for eTag, mod-time, size, filename, extionstion,
	 * mime-type, set headers, and finally output the file's contents
	 *
	 * @param string $file Name of requested file
	 */
	public function __construct($file, $use_include_path = false)
	{
		$this->openFile($file, $use_include_path);
		if (@is_string($this->absolute_path)) {
			$this->etag = md5_file($this->absolute_path);
			$this->mod_time = filemtime($this->absolute_path);
			$this->size = filesize($this->absolute_path);
			$this->typeByExtension();
			$this->cacheControl();
			$this->makeHeaders();
			readfile($this->absolute_path);
			exit();
		} else {
			$this->status = 404;
			$this->HTTPStatus();
		}
	}

	/**
	 * Where most of the headers are set
	 *
	 * Will not reach this point if already have a valid cached copy
	 * Sets Contet-Type, Content-Length,Content-Encoding, Last-Modified,
	 * Etag, and Cache-Control
	 *
	 * @param void
	 * @return void
	 */
	protected function makeHeaders()
	{
		$this->HTTPStatus();
		header("Content-Type: {$this->type}");
		header("Content-Length: {$this->size}");
		if (in_array($this->extension, ['svgz', 'cssz', 'jsz'])) {
			header('Content-Encoding: gzip');
		}
		header("Last-Modified: " . gmdate($this::DATE_FORMAT, $this->mod_time));
		header("Etag: {$this->etag}");
		header('Cache-Control: public');
	}

	/**
	 * The actual cache control done here
	 *
	 * Check and compare headers & respond appropriately
	 *
	 * @param void
	 * @return void
	 */
	protected function cacheControl()
	{
		$etagHeader = (isset($_SERVER['HTTP_IF_NONE_MATCH'])
			? trim($_SERVER['HTTP_IF_NONE_MATCH'])
			: false
		);

		//check if page has changed. If not, send 304 and exit
		if (
			@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $this->mod_time
			|| $etagHeader == $this->etag
		) {
			$this->status = 304;
			$this->HTTPStatus();
		}
	}

	/**
	 * Get mime-type from extension or finfo()
	 *
	 * First, try go through a list of unrecognized extensions.
	 * If not one of those, use the default finfo() method
	 *
	 * @param void
	 * @return void
	 */
	protected function typeByExtension()
	{
		/*
		 * PHP does a fairly poor job of getting MIME-type correct.
		 * Switch on the extension to get MIME-type for unsupported
		 * types. If not one of these, use finfo to guess.
		 */
		switch($this->extension) { //Start by matching file extensions
			case 'svg':
			case 'svgz':
				$this->type = 'image/svg+xml';
				break;

			case 'woff':
				$this->type = 'application/font-woff';
				break;

			case 'otf':
				$this->type = 'application/x-font-opentype';
				break;

			case 'sql':
				$this->type = 'text/x-sql';
				break;

			case 'appcache':
				$this->type = 'text/cache-manifest';
				break;

			case 'mml':
				$this->type = 'application/xhtml+xml';
				break;

			case 'ogv':
				$this->type = 'video/ogg';
				break;

			case 'webm':
				$this->type = 'video/webm';
				break;

			case 'ogg':
			case 'oga':
			case 'opus':
				$this->type = 'audio/ogg';
				break;

			case 'flac':
				$this->type = 'audio/flac';
				break;

			case 'm4a':
				$this->type = 'audio/mp4';
				break;

			case 'css':
			case 'cssz':
				$this->type = 'text/css';
				break;

			case 'js':
			case 'jsz':
				$this->type = 'text/javascript';
				break;

			default:		//If not found, try the file's default
				$finfo = new \finfo(FILEINFO_MIME);
				$this->type = preg_replace('/\;.*$/', null, (string)$finfo->file($this->absolute_path));
		}
	}

	/**
	 * Set HTTP status & exit if no 2##
	 *
	 * @param void
	 * @return void
	 */
	protected function HTTPStatus()
	{
		http_response_code($this->status);
		if (!preg_match('/^2[\d]{2}$/', $this->status)) {
			exit();
		}
	}
}
