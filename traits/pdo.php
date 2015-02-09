<?php
namespace shgysk8zer0\Core\Traits;

use \shgysk8zer0\Core\Resources\Parser as Parser;


/**
 * @see http://php.net/manual/en/pdo.construct.php
 */
trait PDO
{
	public $connected = false;

	protected $dsn;
	protected $user;
	protected $password;
	protected $database;

	public static $ext = 'json';

	final protected function getCreds($con)
	{
		try {
			if (is_string($con)) {
				$tmp_ext = Parser::$DEFAULT_EXT;
				Parser::$DEFAULT_EXT = $this::$ext;
				$creds = Parser::parse($con);
				Parser::$DEFAULT_EXT = $tmp_ext;
				unset($tmp_ext);
			} elseif (is_object($con)) {
				$creds = $con;
			} elseif (is_array($con)) {
				$creds = (object)$con;
			}

			if (is_null($creds) or !is_object($creds)) {
				throw new \Exception('Unable to parse credentials.');
			} elseif (is_null($creds->user)) {
				throw new \Exception('User not given in credentials');
			} elseif (is_null($creds->password)) {
				throw new \Exception('Password not given in credentials');
			}

			$this->user = $creds->user;
			$this->password = $creds->password;
			$this->database = (@is_string($creds->database))
				? $creds->database
				: $this->user;

			if (
				isset($creds->server)
				and array_key_exists('SERVER_ADDR', $_SERVER)
				and $creds->server === $_SERVER['SERVER_ADDR']
			) {
				unset($creds->server);
			}

			if (
				isset($creds->port)
				and (
					!isset($creds->server)
					or $creds->server === $this::DEFAULT_SERVER
				)
			) {
				unset($creds->port);
			}

			$this->dsn = (isset($creds->type)) ? "{$creds->type}:" : 'mysql:';
			$this->dsn .= "dbname={$this->database}";

			if (isset($creds->server)) {
				$this->server = $creds->server;
				$this->dsn .= ";host={$this->server}";
			}

			if (
				isset($creds->port)
				and isset($creds->server)
				and $creds->server !== $this::DEFAULT_SERVER
			) {
				$this->dsn .= ";port={$creds->port}";
			}
		} catch(\Exception $e) {
			trigger_error($e->getMessage, E_USER_WARNING);
		}
	}
}
