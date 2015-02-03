<?php
/**
 * Wrapper for standard PDO class. Allows
 * standard MySQL to be used, while giving benefits
 * of chained prepare->bind->execute...
 *
 * @author Chris Zuber <shgysk8zer0@gmail.com>
 * @package shgysk8zer0\Core
 * @version 0.9.0
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
class PDO
	extends \shgysk8zer0\Core\Resources\PDO_Resources
	implements \shgysk8zer0\Core\Depreciated\Interfaces\PDO
{
	use \shgysk8zer0\Core\Depreciated\Traits\PDO;
}
