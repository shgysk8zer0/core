<?php
/**
 * @author Chris Zuber <shgysk8zer0@gmail.com>
 * @package shgysk8zer0\Core
 * @link http://php.net/manual/en/function.mail.php
 * @link http://manpages.ubuntu.com/manpages/intrepid/man5/ssmtp.conf.5.html
 * @version 1.0.0
 * @copyright 2014, Chris Zuber
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
 * Simple PHP mail class using mail()
 *
 * Strips, converts, etc. Automatically sets required headers.  Takes most
 * of the work out of the coding side of writing emails.
 *
 * You should verify that you have an email service installed.
 * On Ubuntu, I use ssmtp (see link for manpage)
 * @see https://php.net/manual/en/function.mail.php
 * @example
 * //Can be either a comma separated string or an array
 * $to = ['user1@domain.com', 'user2@domain.com'];
 * $subject = 'Sending email with PHP!';
 * $message = file_get_contents('/path/to/someFile.html');
 * $additional_headers = [
 * 		'content-type' => [
 * 			'text/html',
 * 			'charset' => $email::CHARSET
 * 		]
 * 	];
 *
 * $mail = new \shgysk8zer0\Core\email($to, $subject, $message, $additional_headers);
 *
 * $success = $mail->send(true);
 */
class email implements API\Interfaces\Magic_Methods
{
	use API\Traits\Filters;
	use API\Traits\Magic_Methods;

	/**
	 * Email address to send to
	 * @var string
	 */
	public $to = null;

	/**
	 * Email address to send from
	 * @var string
	 */
	public $from = null;

	/**
	 * Subject of the email
	 * @var string
	 */
	public $subject = null;

	/**
	 * Email body
	 * @var string
	 */
	public $message = null;

	/**
	 * Array of additional headers, such as Reply-To
	 * @var array
	 */
	protected $additional_headers = [];

	/**
	 * Can be used to pass additional flags as command line options
	 * @var array
	 */
	protected $additional_paramaters = [];

	/**
	 * Default array of headers
	 * @var array
	 */
	protected $default_headers = [];

	const WRAP_AT = 70;
	const MAX_LENGTH = 1000;
	const HEADER_FILTER = '^A-z\-';
	const NL = "\r\n";
	const CHARSET = 'UTF-8';
	const CONTENT_TYPE = 'text/plain';
	const MIME_VERSION = '1.0';
	const HTML_DOCTYPE = '<!doctype html>';
	const MAGIC_PROPERTY = 'additional_headers';

	/**
	 * Initialize the class and set its default variable from arguments
	 * $default_headers is built dynamically based on SERVER_ADMIN and
	 *
	 * @param mixed $to                     String of array of recipients
	 * @param string $subject               The subject of the email
	 * @param string $message               Email body text
	 * @param array  $additional_headers    Things like 'From', 'To', etc
	 * @param array  $additional_paramaters Additional command line arguments
	 */
	public function __construct(
		$to = null,
		$subject = null,
		$message = null,
		array $additional_headers = array(),
		array $additional_paramaters = array()
	)
	{
		$this->to = $to;
		$this->subject = $subject;
		$this->message = $message;
		$this->default_headers = [
			'MIME-Version' => $this::MIME_VERSION,
			'Content-Type' => [
				$this::CONTENT_TYPE,
				'charset' => $this::CHARSET
			],
			//'To' => $this->recepients(),
			'From' => array_key_exists('SERVER_ADMIN', $_SERVER) ? $_SERVER['SERVER_ADMIN'] : null,
			'X-Mailer' => 'PHP/' . PHP_VERSION
		];
		if (is_array($additional_headers)) {
			$this->additional_headers = $additional_headers;
		}

		if (is_array($additional_paramaters)) {
			$this->additional_paramaters = $additional_paramaters;
		}
	}

	/**
	 * Headers are in two arrays -- default_headers & additional headers
	 * Headers in mail() are to be in the format: "Key: Value\r\n"
	 * Convert [$key => $value] on merging the two header array into the
	 * required format.
	 *
	 * @param void
	 * @return string
	 */
	protected function convertHeaders()
	{
		$headers = array_filter(array_merge(
			$this->default_headers, $this->additional_headers
		));

		return join($this::NL, array_map(function($key, $value) {
			$key = preg_replace('/[' . $this::HEADER_FILTER . ']/', '-', strtoupper($key));
			if (is_array($value)) {
				return "{$key}:" .  join('; ', array_map(function($sub_key, $sub_value) {
					return (is_string($sub_key)) ? "{$sub_key}={$sub_value}" : "{$sub_value}";
				}, array_keys($value), array_values($value)));
			} elseif (is_string($value)) {
				return "{$key}: $value";
			}
		}, array_keys($headers), array_values($headers)));
	}

	/**
	 * Documentation lacks usage of $additional_paramaters, so this is just
	 * a basic array to string conversion by joining with newlines
	 *
	 * @return string
	 */
	protected function convertParamaters()
	{
		return join(PHP_EOL, $this->additional_paramaters);
	}

	/**
	 * $to is mixed, and may be a comma separated string or an array
	 * In either case, we want the output to be a string of valid email
	 * addresses separated by a ",".
	 *
	 * @param void
	 * @return string [user1@domain.com, user2@domain.com]
	 */
	protected function recepients()
	{
		if (is_string($this->to)) {
			return join(', ', array_filter(explode(', ', $this->to), [$this ,'isEmail']));
		} elseif (is_array($this->to)) {
			return join(', ', array_filter($this->to, [$this, 'isEmail']));
		} elseif (is_object($this->to)) {
			return join(', ', array_filter(get_object_vars($this->to), [$this, 'isEmail']));
		}
	}

	/**
	 * Most emails should be either plain text or HTML (no attachment support)
	 *
	 * The suggested length for plain text is 70 characters, and we will
	 * want to remove any HTML tags
	 *
	 * If this is an HTML message, we will want to wrap all lines to a max
	 * length of 1,000 (sometimes a requirement, better safe than sorry)
	 *
	 * In both cases, the only valid newline charter is "\r\n", so replace
	 * default newline with that.
	 *
	 * @param  bool $html   Whether or not HTML output is desired
	 * @return string
	 */
	protected function convertMessage($html = false)
	{
		return str_replace(
			PHP_EOL,
			$this::NL,
			($html) ?
				wordwrap(
					$this->asHTML(),
					$this::MAX_LENGTH,
					$this::NL
				) : wordwrap(
					strip_tags($this->message),
					$this::WRAP_AT,
					$this::NL
				)
		);
	}

	/**
	 * Converts a subject into a wrapped special character safe string
	 *
	 * @param void
	 * @return string
	 */
	protected function trimSubject()
	{
		return wordwrap(
			strip_tags($this->subject),
			$this::WRAP_AT,
			$this::NL
		);
	}

	/**
	 * Build an entire HTML document for message using \DOMDocuemnt
	 *
	 * @param void
	 * @return string
	 */
	protected function asHTML()
	{
		$dom = new \DOMDocument('1.0', $this::CHARSET);
		$dom->loadHTML($this::HTML_DOCTYPE . $this->message);
		$html = $dom->getElementsByTagName('html')->item(0);
		$body = $dom->getElementsByTagName('body')->item(0);
		$head = $dom->createElement('head');
		$html->insertBefore($head, $body);
		$meta = $dom->createElement('meta');
		$head->appendChild($meta);
		$charset = $dom->createAttribute('charset');
		$charset->value = $this::CHARSET;
		$meta->appendChild($charset);
		$title = $dom->createElement('title', $this->trimSubject());
		$head->appendChild($title);
		return $dom->saveHTML();
	}

	/**
	 * Sends the email.
	 *
	 * All trimming, wrapping, converting, etc takes place here. Prior to
	 * this, the class variables are their original values. None are updated
	 * in this method, mail() only gets the output of the conversions.
	 *
	 * @param  boolean $html Is this an HTML email?
	 * @return boolean       Success of mail()
	 */
	public function send($html = false)
	{
		if ($html) {
			$this->additional_headers['Content-Type'] = [
				'text/html',
				'charset' => $this::CHARSET
			];
		}

		return mail(
			$this->recepients(),
			$this->trimSubject(),
			$this->convertMessage($html),
			$this->convertHeaders(),
			$this->convertParamaters()
		);
	}
}
