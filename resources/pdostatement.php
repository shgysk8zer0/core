<?php
namespace shgysk8zer0\Core\Resources;

final class PDOStatement extends \PDOStatement
{
	protected function __construct(\PDO $PDO)
	{
		//PDOStatement has no __construct, but extending class must have one.
	}

	public function __set($key, $value)
	{
		parent::bindParam(":{$key}", $value);
	}

	public function bind(array $binders = array())
	{
		foreach ($this->bindKeys($binders) as $key => $value) {
			$this->bindParam($key, $value);
		}
		return $this;
	}

	private function bindKeys(array $binders)
	{
		return array_combine(
			array_map(function($key)
				{
					return ':' . str_replace('`', '``', $key);
				},
				array_keys($binders)
			),
			array_values($binders)
		);
	}

	public function execute($bound_input_params = null)
	{
		if (is_array($bound_input_params)) {
			$bound_input_params = $this->bindKeys($bound_input_params);
		}

		return parent::execute($bound_input_params);
	}
}
