<?php
/**
 * @author Chris Zuber
 * @package shgysk8zer0\Core
 * @version 1.0.0
 * @copyright 2016, Chris Zuber
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
 * Class providing properties compatible with the JavaScript implementation
 * of the Notification API.
 * @see https://developer.mozilla.org/en-US/docs/Web/API/notification/Notification
 */
class Notification implements API\Interfaces\String
{
	use API\Traits\Notification;
	use API\Traits\Magic_Methods;
	use API\Traits\Magic\Call_Setter;

	const MAGIC_PROPERTY   = 'options';
	// const RESTRICT_SETTING = true;

	/**
	 * Creates a new instance of the Notification class
	 *
	 * @param string $title   Title of the notificaiton
	 * @param array  $options Array of options, such as body and icon
	 */
	final public function __construct($title, array $options = array())
	{
		$this->title = $title;
		$this->options = array_merge($this->{self::MAGIC_PROPERTY}, $options);
		$this->options = array_filter($this->{self::MAGIC_PROPERTY}, [$this, '_filterOptions']);
	}

	/**
	 * Converts class into '{$title, {"body": $body, ...}}'
	 *
	 * @return string JSON encoded string
	 */
	final public function __toString()
	{
		return json_encode($this);
	}

	/**
	 * Private method to filter out unset options
	 *
	 * @param  mixed  $option [description]
	 * @return boolean        [description]
	 */
	final private function _filterOptions($option)
	{
		return !(is_string($option) && strlen($option) === 0);
	}
}
