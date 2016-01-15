<?php
namespace shgysk8zer0\Core;
use \shgysk8zer0\Core\Exceptions\HTTP as HTTPException;
use \shgysk8zer0\Core_API\Abstracts\HTTPStatusCodes as HTTP;
use \shgysk8zer0\Core_API as API;
use \shgysk8zer0\Core as Core;
class RESTAPI {
	use API\Traits\Magic_Methods;
	const MAGIC_PROPERTY = '_response';
	public $method;
	public $url;
	public $headers;
	public $request;
	private $_response = array();
	private $_errors = array();
	private $_exceptions = array();
	private $_accepts = array('application/json');

	public function __construct(
		Array $allowed_methods = array('GET', 'POST', 'OPTIONS', 'HEAD')
	) {
		try {
			$this->method = $_SERVER['REQUEST_METHOD'];
			$this->headers = new Core\Headers();
			if ($this->method === 'OPTIONS') {
				$this->headers->allow = join(',', $allowed_methods);
				$this->headers->content_type = 'httpd/unix-directory';
				exit();
			}
			if (! in_array($this->method, $allowed_methods)) {
				throw new HTTPException(
					sprintf('Method %s not allowed', $this->method),
					HTTP::METHOD_NOT_ALLOWED
				);
			} elseif (! in_array($this->headers->accept, $this->_accepts)) {
				throw new HTTPException(
					sprintf(
						'Cannot respond with acceptable Content-Type. Use: [%s]',
						join(', ', $this->_accepts)
					),
					HTTP::BAD_REQUEST
				);
			}
			$this->url = new Core\URL();
			switch ($this->method) {
				case 'GET':
					$this->request = $_GET;
					break;
				case 'POST':
					$this->request = $_POST;
					break;
				case 'PUT':
				case 'DELETE':
				case 'TRACE':
					$this->request = $this->_parseRequest();
					$this->{self::MAGIC_PROPERTY} = $this->request;
					exit();
			}
		} catch (HTTPException $e) {
			$e();
		} catch (\Exception $e) {
			http_response_code(HTTP::INTERNAL_SERVER_ERROR);
			exit();
		}
	}

	public function __toString() {
		return json_encode($this->{self::MAGIC_PROPERTY});
	}

	final public function __destruct() {
		if (isset($this->headers->accept)) {
			exit($this);
		}
	}

	private function _parseRequest() {
		try {
			if ($this->method === 'GET') {
				return $_GET;
			} elseif ($this->method === 'POST') {
				return $_POST;
			} else {
				$body = file_get_contents('php://input');
				if (! isset($this->headers->content_length)) {
					throw new HTTPException('Missing Content-Length header', HTTP::LENGTH_REQUIRED);
				} elseif (strlen($body) !== $this->headers->content_length) {
					throw new HTTPException('Invalid Content-Length', HTTP_BAD_REQUEST);
				} else {
					switch ($this->headers->content_type) {
						case 'application/x-www-form-urlencoded':
							return parse_url("?$body");
						case 'application/json':
							return json_decode($body);
						case 'application/xml':
							return simplexml_load_string($body);
						case 'text/plain';
							return $body;
						default:
							throw new HTTPException(
								sprintf('Unsupported Content-Type: %s', $this->headers->content_type),
								HTTP::UNSUPPORTED_MEDIA_TYPE
							);
					}
				}
			}
		} catch (HTTPException $e) {
			$e();
		} catch (\Exception $e) {
			http_response_code(HTTP::INTERNAL_SERVER_ERROR);
			exit;
		}
	}
}
