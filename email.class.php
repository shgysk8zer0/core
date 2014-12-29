<?php
	/**
	 * Simple PHP mail class using mail()
	 *
	 * Strips, converts, etc. Automatically sets required headers.  Takes most
	 * of the work out of the coding side of writing emails.
	 *
	 * You should verify that you have an email service installed.
	 * On Ubuntu, I use ssmtp (see link for manpage)
	 *
	 * @author Chris Zuber <shgysk8zer0@gmail.com>
	 * @package core_shared
	 * @link http://php.net/manual/en/function.mail.php
	 * @link http://manpages.ubuntu.com/manpages/intrepid/man5/ssmtp.conf.5.html
	 * @version 2014-06-05
	 *
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
	 * $mail = new \shgysk8zer0\core\email($to, $subject, $message, $additional_headers);
	 *
	 * $success = $mail->send(true);
	 *
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

	namespace shgysk8zer0\core;
	class email {
		public $to = null, $from = null, $subject = null, $message = null;
		protected $additional_headers = [], $additional_paramaters = [],
		$default_headers = null;

		const WRAP_AT = 70,
			MAX_LENGTH = 1000,
			HEADER_FILTER = '^A-z\-',
			NL = "\r\n",
			CHARSET = 'UTF-8',
			CONTENT_TYPE = 'text/plain',
			MIME_VERSION = '1.0',
			HTML_DOCTYPE = '<!doctype html>';

		/**
		 * Initialize the class and set its default variable from arguments
		 * $default_headers is built dynamically based on SERVER_ADMIN and
		 *
		 *
		 * @param mixed $to                     [String of array of recipients]
		 * @param string $subject               [The subject of the email]
		 * @param string $message               [Email body text]
		 * @param array  $additional_headers    [Things like 'From', 'To', etc]
		 * @param array  $additional_paramaters [Additional command line arguments]
		 */

		public function __construct(
			$to = null,
			$subject = null,
			$message = null,
			array $additional_headers = null,
			array $additional_paramaters = null
		) {
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
			if(is_array($additional_headers)) {
				$this->additional_headers = $additional_headers;
			}
			if(is_array($additional_paramaters)) {
				$this->additional_paramaters = $additional_paramaters;
			}
		}

		/**
		 * Set a value in $additional_headers using Magic Method
		 *
		 * @param string $key  [Key in $additional_headers]
		 * @param mixed $value [Value to set it to]
		 * @return void
		 *
		 * @example $mail->From = 'user@domain.com';
		 */

		public function __set($key, $value) {
			$this->additional_headers[str_replace('_', '-', $key)] = $value;
		}

		/**
		 * Retrieve a value from $additional_headers using Magic Method
		 *
		 * @param string $key [Key in $additional_headers]
		 * @return mixed      [The value of $additional_headers[$key]]
		 *
		 * @example echo $mail->From;
		 */

		public function __get($key) {
			return ($this->__isset($key)) ? $this->additional_headers[str_replace('_', '-', $key)] : null;
		}

		/**
		 * Check existance of $key in $additional_headers using Magic Method
		 *
		 * @param string $key [Key in $additional_headers]
		 * @return boolean
		 *
		 * @example isset($mail->From)
		 */

		public function __isset($key) {
			return (array_key_exists(str_replace('_', '-', $key), $this->additional_headers));
		}

		/**
		 * Remove from $additional_headers using Magic Method
		 *
		 * @param string $key [Key in $additional_headers]
		 * @return void
		 *
		 * @example unset($mail->From)
		 */

		public function __unset($key) {
			if($this->__isset($key)) unset($this->additional_headers[str_replace('_', '-', $key)]);
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

		protected function convert_headers() {
			$headers = array_filter(array_merge($this->default_headers, $this->additional_headers));

			return join($this::NL, array_map(function($key, $value) {
				$key = preg_replace('/[' . $this::HEADER_FILTER . ']/', '-', strtoupper($key));
				if(is_array($value)) {
					return "{$key}:" .  join('; ', array_map(function($sub_key, $sub_value) {
						return (is_string($sub_key)) ? "{$sub_key}={$sub_value}" : "{$sub_value}";
					}, array_keys($value), array_values($value)));
				}
				elseif(is_string($value)) {
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

		protected function convert_paramaters() {
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

		protected function recepients() {
			if(is_string($this->to)) {
				return join(', ', array_filter(explode(', ', $this->to), [$this ,'is_email']));
			}
			elseif(is_array($this->to)) {
				return join(', ', array_filter($this->to, [$this, 'is_email']));
			}
			elseif(is_object($this->to)) {
				return join(', ', array_filter(get_object_vars($this->to), [$this, 'is_email']));
			}
		}

		/**
		 * Verify that $address is a valid email address
		 *
		 * @param  string  $address [Text which should be an email address]
		 * @return boolean          [Whether or not it matches the specification]
		 */

		public function is_email($address) {
			return filter_var($address, FILTER_VALIDATE_EMAIL);
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
		 * @param  bool $html   [Whether or not HTML output is desired]
		 * @return string
		 */

		protected function convert_message($html = false) {
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

		protected function trim_subject() {
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

		protected function asHTML() {
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
			$title = $dom->createElement('title', $this->trim_subject());
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
		 * @param  boolean $html [Is this an HTML email?]
		 * @return boolean       [Success of mail()]
		 */

		public function send($html = false) {
			if($html) {
				$this->additional_headers['Content-Type'] = [
					'text/html',
					'charset' => $this::CHARSET
				];
			}
			return mail(
				$this->recepients(),
				$this->trim_subject(),
				$this->convert_message($html),
				$this->convert_headers(),
				$this->convert_paramaters()
			);
		}
	}
?>
