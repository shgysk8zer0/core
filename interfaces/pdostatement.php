<?php
namespace shgysk8zer0\Core\Interfaces;

interface PDOStatement
{
	/**
	* Binds values to prepared statements
	*
	* @param array $array    [:key => value]
	* @return self
	* @example $pdo->prepare(...)->bind([
	* 	'col_name' => $value,
	* 	'col2' => 'something else'
	* ])
	*/
	public function bind(array $array);

	/**
	* Executes prepared statements. Does not return results
	*
	* @param void
	* @return self
	*/

	public function execute();

	/**
	* Gets results of prepared statement. $n can be passed to retreive a specific row
	*
	* @param int $n   [Optional index for single result to return]
	* @return mixed
	*/
	public function get_results($n = null);
}
