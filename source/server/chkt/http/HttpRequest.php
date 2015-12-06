<?php

namespace chkt\http;



class HttpRequest {
	
	/**
	 * The version string
	 */
	const VERSION = '0.0.7';
	
	/**
	 * The http protocol
	 */
	const PROTOCOL_HTTP  = 'http';
	/**
	 * The https protocol
	 */
	const PROTOCOL_HTTPS = 'https';
	
	/**
	 * The get method
	 */
	const METHOD_GET    = 'GET';
	/**
	 * The post method
	 */
	const METHOD_POST   = 'POST';
	/**
	 * The put method
	 */
	const METHOD_PUT    = 'PUT';
	/**
	 * The patch method
	 */
	const METHOD_PATCH  = 'PATCH';
	/**
	 * The delete method
	 */
	const METHOD_DELETE = 'DELETE';
	
	/**
	 * The html mime type
	 */
	const MIME_HTML  = 'text/html';
	/**
	 * The xhtml mime type
	 */
	const MIME_XHTML = 'application/xml+html';
	/**
	 * The form-urlencoded mime type
	 */
	const MIME_FORM  = 'application/x-www-form-urlencoded';
	/**
	 * The xml mime type
	 */
	const MIME_XML   = 'application/xml';
	/**
	 * The json mime type
	 */
	const MIME_JSON  = 'application/json';
	
	/**
	 * The utf-8 encoding
	 */
	const ENC_UTF8 = 'utf-8';
	
	
	
	/**
	 * The origin request properties
	 * @var mixed[]
	 */
	static protected $_origin = [];

	
	/**
	 * The request properties
	 * @var string[]
	 */
	protected $_property = [];
	
	
	
	/**
	 * DEPRECATED - use either a provider or new HttpRequest()
	 * Returns an instance reprenting the origin request
	 * @param HttpRequest $target The target instance
	 * @return HttpRequest
	 */
	static public function Origin(HttpRequest &$target = null) {
		if (is_null($target)) $target = new HttpRequest();
		else $target->_construct();
		
		return $target;
	}
	
	
	static private function _parseHeader($raw, $attr = '__default__') {
		$data = [];
		
		parse_str('__default__=' . str_replace(';', '&', $raw), $data);
		
		return array_key_exists($attr, $data) ? $data[$attr] : '';
	}
	
	
	static private function _parseAccept($attr, $pattern) {
		$items = explode(',', $attr);
		
		$res = [];
		
		foreach ($items as $item) {
			$match = [];
			
			if (!preg_match('/^(' . $pattern . ')(?:;q=(0\\.\\d+))?$/', $item, $match)) continue;
			
			switch (count($match)) {
				case 2 : 
					$res[$match[1]] = 1.0;
					
					break;
				
				case 3 :
					$res[$match[1]] = (float) $match[2];
					
					break;
				
				default : throw new \ErrorException();
			}
		}
		
		arsort($res, SORT_NUMERIC);
		
		return $res;
	}
	
	
	/**
	 * Returns the origin request timestamp
	 * @return int
	 */
	static public function originTime() {
		$origin =& self::$_origin;
		
		if (!array_key_exists('time', $origin)) $origin['time'] = $_SERVER['REQUEST_TIME'];		//php does not return anything when using filter_input()
		
		return $origin['time'];
	}
	
	/**
	 * DEPRECATED - use ::originTime instead
	 * Returns the origin request timestamp
	 * @return uint
	 */
	static public function time() {
		return $_SERVER['REQUEST_TIME'];		//php does not return anything when using filter_input()
		//return filter_input(INPUT_SERVER, 'REQUEST_TIME');
	}
	
	
	/**
	 * Returns the origin request protocol
	 * @return string
	 */
	static public function originProtocol() {
		$origin =& self::$_origin;
		
		if (!array_key_exists('protocol', $origin)) {
			switch (filter_input(INPUT_SERVER, 'HTTPS')) {
				case '' :
				case 'off' :
					$origin['protocol'] = 'http';
					
					break;
				
				default : $origin['protocol'] = 'https';
			}
		}
		
		return $origin['protocol'];
	}
	
	/**
	 * Returns the origin request host name
	 * @return string
	 */
	static public function originHostName() {
		$origin =& self::$_origin;
		
		if (!array_key_exists('hostName', $origin)) $origin['hostName'] = filter_input(INPUT_SERVER, 'SERVER_NAME');
		
		return $origin['hostName'];
	}
	
	/**
	 * Returns the origin request http method
	 * @return string
	 */
	static public function originMethod() {
		$origin =& self::$_origin;
		
		if (!array_key_exists('method', $origin)) $origin['method'] = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
		
		return $origin['method'];
	}
	
	/**
	 * Returns the origin request accepted mime types
	 * @return string[]
	 */
	static public function originAcceptMimes() {
		$origin =& self::$_origin;
		
		if (!array_key_exists('acceptMimes', $origin)) $origin['acceptMimes'] = self::_parseAccept(filter_input(INPUT_SERVER, 'HTTP_ACCEPT'), '[a-z]+\\/[a-z]+|\\*\\/\\*');
			
		return $origin['acceptMimes'];
	}
	
	/**
	 * Returns the origin request body mime type
	 * @return string
	 */
	static public function originMime() {
		$origin =& self::$_origin;
		
		if (!array_key_exists('mime', $origin)) $origin['mime'] = self::_parseHeader(filter_input(INPUT_SERVER, 'CONTENT_TYPE'));
		
		return $origin['mime'];
	}
	
	/**
	 * Returns the origin request body charset
	 * @return string
	 */
	static public function originEncoding() {
		$origin =& self::$_origin;
		
		if (!array_key_exists('encoding', $origin)) $origin['encoding'] = self::_parseHeader(filter_input(INPUT_SERVER, 'CONTENT_TYPE'), 'charset');
		
		return $origin['encoding'];
	}
	
	/**
	 * Returns the origin request accepted languages
	 * @return string[]
	 */
	static public function originAcceptLanguages() {
		$origin =& self::$_origin;
		
		if (!array_key_exists('acceptLanguages', $origin)) $origin['acceptLanguages'] = self::_parseAccept(filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE'), '[A-Za-z]{2}(?:-[A-Za-z]{2})?');
		
		return $origin['acceptLanguages'];
	}
	
	/**
	 * Returns the origin request path
	 * @return string
	 */
	static public function originPath() {
		$origin =& self::$_origin;
		
		if (!array_key_exists('path', $origin)) $origin['path'] = parse_url(filter_input(INPUT_SERVER, 'REQUEST_URI'), PHP_URL_PATH);
		
		return $origin['path'];
	}
	
	/**
	 * Returns the origin request query
	 * @return string[]
	 */
	static public function originQuery() {
		$origin =& self::$_origin;
		
		if (!array_key_exists('query', $origin)) {
			$origin['query'] = [];
			
			parse_str(filter_input(INPUT_SERVER, 'QUERY_STRING'), $origin['query']);
		}
		
		return $origin['query'];
	}
	
	/**
	 * Returns the origin request body
	 * @return string
	 */
	static public function originBody() {
		$origin =& self::$_origin;
		
		if (!array_key_exists('body', $origin)) {
			$handle = fopen('php://input', 'r');
		
			$origin['body'] = stream_get_contents($handle);
			
			fclose($handle);
		}
		
		return $origin['body'];
	}
	
	
	/**
	 * Returns the origin request client user agent
	 * @return string
	 */
	static public function originClientUA() {
		$origin =& self::$_origin;
		
		if (!array_key_exists('clientUA', $origin)) $origin['clientUA'] = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT');
		
		return $origin['clientUA'];
	}
	
	/**
	 * Returns the origin request client ip address
	 * @return string
	 */
	static public function originClientIP() {
		$origin =& self::$_origin;
		
		if (!array_key_exists('clientIP', $origin)) $origin['clientIP'] = filter_input(INPUT_SERVER, 'REMOTE_ADDR');
		
		return $origin['clientIP'];
	}
	
	/**
	 * Returns true if the origin request is asynchronous, false otherwise
	 * @return bool
	 */
	static public function originClientAsync() {
		$origin =& self::$_origin;
		
		if (!array_key_exists('clientAsync', $origin)) $origin['clientAsync'] = filter_input(INPUT_SERVER, 'HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest';
		
		return $origin['clientAsync'];
	}
	
	
	/**
	 * Returns <code>true</code> if <code>$protocol</code> is a valid protocol, <code>false</code> otherwise
	 * @param string $protocol The protocol
	 * @return bool
	 */
	static public function isProtocol($protocol) {
		return in_array($protocol, [
			self::PROTOCOL_HTTP,
			self::PROTOCOL_HTTPS
		]);
	}
	
	/**
	 * Returns <code>true</code> if <code>$method</code> is a valid method, <code>false</code> otherwise
	 * @param string $method The http method
	 * @return bool
	 */
	static public function isMethod($method) {
		return in_array($method, [
			self::METHOD_GET,
			self::METHOD_POST,
			self::METHOD_PUT,
			self::METHOD_PATCH,
			self::METHOD_DELETE
		]);
	}
	
	/**
	 * Returns <code>true</code> if <code>$mime</code> is a valid mime type, <code>false</code> otherwise
	 * @param string $mime
	 * @return bool
	 */
	static public function isMime($mime) {
		return in_array($mime, [
			self::MIME_HTML,
			self::MIME_XHTML,
			self::MIME_FORM,
			self::MIME_JSON,
			self::MIME_XML
		]);
	}
	
	/**
	 * Returns true if $charset is a valid mime type, false otherwise
	 * @param string $charset
	 * @return bool
	 */
	static public function isEncoding($charset) {
		return in_array($charset, [
			self::ENC_UTF8
		]);
	}
	
	
	
	/**
	 * The constructor
	 * @param string[] $properties The instance properties
	 */
	public function __construct(Array $properties = []) {
		$this->_property = $properties;
	}
	
	
	/**
	 * Returns the instance timestamp
	 * @return uint
	 */
	public function getTime() {
		return array_key_exists('time', $this->_property) ? $this->_property['time'] : self::originTime();
	}
	
	/**
	 * Sets the instance timestamp
	 * @param uint $time The timestamp
	 * @return HttpRequest
	 * @throws \ErrorException if <code>$time</code> is not a <code>uint</code>
	 */
	public function setTime($time) {
		if (!is_int($time) || $time < 0) throw new \ErrorException();
		
		$this->_property['time'] = $time;
		
		return $this;
	}
	
	
	/**
	 * Returns the instance protocol
	 * @return string
	 */
	public function getProtocol() {
		return array_key_exists('protocol', $this->_property) ? $this->_property['protocol'] : self::originProtocol();
	}
	
	/**
	 * Sets the instance protocol
	 * @param string $protocol The protocol
	 * @return HttpRequest
	 * @throws \ErrorException if <code>$protocol</code> is not a valid protocol
	 */
	public function setProtocol($protocol) {
		if (!self::isProtocol($protocol)) throw new \ErrorException();
		
		$this->_property['protocol'] = $protocol;
		
		return $this;
	}
	
	
	/**
	 * Returns the instance host name
	 * @return string
	 */
	public function getHostName() {
		return array_key_exists('name', $this->_property) ? $this->_property['name'] : self::originHostName();
	}
	
	/**
	 * Sets the instance host name
	 * @param string $name The instance host name
	 * @return HttpRequest
	 * @throws \ErrorException if <code>$name</code> is not a <em>nonempty</em> <code>String</code>
	 */
	public function setHostName($name) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();
		
		$this->_property['name'] = $name;
		
		return $this;
	}
	
	
	/**
	 * Returns the instance http method
	 * @return string
	 */
	public function getMethod() {
		return array_key_exists('method', $this->_property) ? $this->_property['method'] : self::originMethod();
	}
	
	/**
	 * Sets the instance http method
	 * @param string $method The http method
	 * @return HttpRequest
	 * @throws \ErrorException if <code>$method</code> is not a valid http method
	 */
	public function setMethod($method) {
		if (!self::isMethod($method)) throw new \ErrorException();
		
		$this->_property['method'] = $method;
		
		return $this;
	}
	
	
	/**
	 * Returns the instance mime type
	 * @return string
	 */
	public function getMime() {
		return array_key_exists('mime', $this->_property) ? $this->_property['mime'] : self::originMime();
	}
	
	/**
	 * Sets the instance mime type
	 * @param string $mime The mime type
	 * @return HttpRequest
	 * @throws \ErrorException if <code>$mime</code> is not a valid mime type
	 */
	public function setMime($mime) {
		if (!self::isMime($mime)) throw new \ErrorException();
		
		$this->_property['mime'] = $mime;
		
		return $this;
	}
	
	
	/**
	 * Returns the instance character encoding
	 * @return string
	 */
	public function getEncoding() {
		return array_key_exists('encoding', $this->_property) ? $this->_property['encoding'] : self::originEncoding();
	}
	
	/**
	 * Sets the instance character encoding
	 * @param string $charset The character encoding
	 * @return HttpRequest
	 * @throws \ErrorException if $charset is not a valid encoding
	 */
	public function setEncoding($charset) {
		if (!self::isEncoding($charset)) throw new \ErrorException();
		
		$this->_property['encoding'] = $charset;
		
		return $this;
	}
	
	
	/**
	 * Returns the accepted mime types of the instance
	 * @return string[]
	 */
	public function getAcceptMimes() {
		return array_key_exists('mimes', $this->_property) ? $this->_property['mimes'] : self::originAcceptMimes();
	}
	
	/**
	 * Sets the accepted mime types of the instance
	 * @param string[] $mimes
	 * @return HttpRequest
	 */
	public function setAcceptMimes(Array $mimes) {
		//IMPLEMENT
		
		return $this;
	}
	
	/**
	 * Returns the prefered mime choice of <code>$mimes</code> or an empty string
	 * @param string[] $mimes The available mime types
	 * @return string
	 */
	public function getPreferedAcceptMime(Array $mimes) {
		$accept = $this->getAcceptMimes();
		
		foreach ($accept as $mime => $score) {
			if (in_array($mime, $mimes)) return $mime;
		}
		
		return '';
	}
	
	
	/**
	 * Returns the accepted languages of the instance
	 * @return string[]
	 */
	public function getAcceptLanguages() {
		return array_key_exists('langs', $this->_property) ? $this->_property['langs'] : self::originAcceptLanguages();
	}
	
	/**
	 * Sets the accepted languages of the instance
	 * @param string[] $langs
	 * @return HttpRequest
	 */
	public function setAcceptLanguages(Array $langs) {
		//IMPLEMENT
		
		return $this;
	}
	
	/**
	 * Returns the prefered language choice of <code>$langs</code> or an empty string
	 * @param string[] $langs The available languages
	 * @return string
	 */
	public function getPreferedAcceptLanguage(Array $langs) {
		$accept = $this->getAcceptLanguages();
		
		foreach ($accept as $lang => $score) {
			if (in_array($lang, $langs)) return $lang;
		}
		
		return '';
	}
	
	
	/**
	 * Returns the path of the instance
	 * @return string
	 */
	public function getPath() {
		return array_key_exists('path', $this->_property) ? $this->_property['path'] : self::originPath();
	}
	
	/**
	 * Sets the path of the instance
	 * @param string $path
	 * @return HttpRequest
	 */
	public function setPath($path) {
		if (!is_string($path)) throw new \ErrorException();
		
		$this->_property['path'] = $path;
		
		return $this;
	}
	
	
	/**
	 * Returns the query of the instance
	 * @return string
	 */
	public function getQuery() {
		return array_key_exists('query', $this->_property) ? $this->_property['query'] : self::originQuery();
	}
	
	/**
	 * Sets the query of the instance
	 * @param array $query
	 * @return HttpRequest
	 */
	public function setQuery(Array $query) {
		if (!array_filter($query, 'is_string')) throw new \ErrorException();
			
		$this->_property['query'] = $query;
		
		return $this;
	}
	
	
	/**
	 * Returns the raw request body of the instance
	 * @return string
	 */
	public function getBody() {
		return array_key_exists('body', $this->_property) ? $this->_property['body'] : self::originBody();
	}
	
	/**
	 * Sets the raw request body of the instance
	 * @param string $body
	 * @throws \ErrorException if $body is not a string
	 * @return HttpRequest
	 */
	public function setBody($body) {
		if (!is_string($body)) throw new \ErrorException();
		
		$property =& $this->_property;
		
		$property['body'] = $body;
		
		unset($property['payload']);
		
		return $this;
	}
	
	/**
	 * Returns a structured representation of the request body of the instance if possible,
	 * the raw request body otherwise
	 * @return mixed
	 */
	public function getPayload() {
		$property =& $this->_property;
		
		if (array_key_exists('payload', $property)) return $property['payload'];
		
		$body = $this->getBody();
		$mime = $this->getMime();
		
		switch($mime) {
			case HttpRequest::MIME_FORM :
				$res = [];
				
				parse_str($body, $res);
				
				return $res;
				
			case HttpRequest::MIME_JSON : 
				return json_decode($body, true);
				
			default :
				return $body;
		}
	}
	
	/**
	 * Sets the structured representation of the request body of the instance
	 * @param mixed $payload
	 * @return HttpRequest
	 */
	public function setPayload($payload) {
		$this->_property['payload'] = $payload;
		
		return $this;
	}
}
