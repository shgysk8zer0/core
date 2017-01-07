<?php
/**
 * @author Chris Zuber <shgysk8zer0@gmail.com>
 * @package shgysk8zer0\Core
 * @version 1.0.0
 * @copyright 2017, Chris Zuber
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

class Observer implements \SplObserver
{
	/**
	 * Callback to call
	 * @var Callable
	 */
	private $_callback;

	public function __construct(Callable $callback)
	{
		$this->_callback = $callback;
	}

	/**
	 * Executes callback using $subject->args
	 * @param  SplSubject $subject
	 * @return void
	 */
	public function update(\SplSubject $subject)
	{
		if (isset($subject->args)){
			call_user_func_array($this->_callback, $subject->args);
		}
	}
}
