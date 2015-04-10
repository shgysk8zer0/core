<?php
namespace shgysk8zer0\Core\Traits;
/**
 * @deprecated
 */
trait Legacy_PDOStatement
{
	final public function get_results($col = null)
	{
		trigger_error(__METHOD__ . ' is deprecated', E_USER_DEPRECATED);
		return $this->getResults($col);
	}
}
