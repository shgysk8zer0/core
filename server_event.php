<?php
/**
 * @author Chris Zuber <shgysk8zer0@gmail.com>
 * @package shgysk8zer0\Core
 * @version 1.0.0
 * @see https://developer.mozilla.org/en-US/docs/Server-sent_events/Using_server-sent_events
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
 * Class to allow continuous updates from server using Server Sent Events
 *
 * @example
 * $event = new server_event(); $n = 42;
 * while($n--) {
 * 	echo $event->notify(
 * 	'This is an example of a server event',
 * 	'It functions the same has json_response, but can send multiple messages'
 * )->html(
 * 	'main',
 * 	'This is the ' . 43 - $n .'th message'
 * )->wait(1)
 * }
 */
final class Server_Event implements API\Interfaces\Magic_Methods, API\Interfaces\AJAX_DOM
{
	use API\Traits\Singleton;
	use API\Traits\Magic_Methods;
	use API\Traits\AJAX_DOM;

	const CONTENT_TYPE = 'text/event-stream';
	const MAGIC_PROPERTY = 'response';
	const DEFAULT_EVENT = 'ping';
	const DELAY = 1;

	/**
	 * Constructor for class. Class method to set headers
	 * and initialize first (optional) set of data.
	 *
	 * Inherits its methods from json_response, so do parent::__construct()
	 *
	 * @param array $data (optional array of data to be initialized with)
	 * @example $event = new server_event(['html' => ['main' => 'It Works!']]...)
	 */
	public function __construct($data = null)
	{
		$this->setHeaders();

		if (is_array($data)) {
			$this->{self::MAGIC_PROPERTY} = $data;
		}
	}

	/**
	 * Send any remaining data when class is destructed
	 *
	 * @param void
	 * @return void
	 */
	public function __destruct()
	{
		if (! empty($this->{self::MAGIC_PROPERTY})) {
			echo 'event: close' . PHP_EOL;
			echo 'data:' . json_encode($this->{self::MAGIC_PROPERTY}) . PHP_EOL . PHP_EOL;
		}
	}

	/**
	 * Returns the current event data when class is converted to string, e.g. echo.
	 *
	 * @param void
	 * @return string
	 * @example echo $event;
	 * @example $var = "$event"
	 * @todo Is there a way to flush after returning? Doesn't work so perfectly
	 */
	public function __toString()
	{
		$json = $this->{self::MAGIC_PROPERTY};
		$this->{self::MAGIC_PROPERTY} = [];
		ob_flush();
		flush();
		return 'event: ' . self::DEFAULT_EVENT . PHP_EOL .
		'data: ' . json_encode($json) . PHP_EOL . PHP_EOL;
	}

	/**
	 * Sends everything with content-type of text/event-stream,
	 * Echoes json_encode($this->response)
	 *
	 * @param void
	 * @return self
	 * @example $event->send()
	 * @deprecated
	 */
	public function send()
	{
		echo $this;
		ob_flush();
		flush();
	}

	/**
	 * Sets headers required to be handled as a server event.
	 * @param void
	 * @return self
	 * @return self
	 */
	private function setHeaders()
	{
		header('Content-Type: ' . self::CONTENT_TYPE);
		header_remove('X-Powered-By');
		header_remove('Expires');
		header_remove('Pragma');
		header_remove('X-Frame-Options');
		header_remove('Server');
		return $this;
	}

	/**
	 * Set delay between events and flush out
	 * previous response.
	 *
	 * @param int $delay
	 * @return self
	 */
	public function wait($delay = self::DELAY) {
		sleep((int)$delay);
		return $this;
	}

	/**
	 * Same as the send() method, except this
	 * method indicates that it is the final event.
	 *
	 * The handler in handleJSON will terminate the serverEvent
	 * after receiving an event of type 'close'
	 *
	 * @param void
	 * @return self
	 * @example $event->close()
	 * @deprecated
	 */
	public function end()
	{
		echo 'event: close' . PHP_EOL;

		if (!empty($this->{self::MAGIC_PROPERTY})) {
			echo 'data: ' . json_encode($this->{self::MAGIC_PROPERTY}) . PHP_EOL . PHP_EOL;
			$this->{self::MAGIC_PROPERTY} = [];
		} else {
			echo 'data: "{}"' . PHP_EOL . PHP_EOL;
		}

		ob_flush();
		flush();
		return $this;
	}
}
