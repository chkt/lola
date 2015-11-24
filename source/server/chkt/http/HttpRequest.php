<?php

namespace chkt\http;



class HttpRequest {
	
	/**
	 * The version string
	 */
	const VERSION = '0.0.6';
	
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
	
	
	
	static protected $_languages = [];
	static protected $_mimes = [];
	
	
	/**
	 * The request properties
	 * @var string[]
	 */
	protected $_property = [];
	
	
	
	/**
	 * Returns an instance reprenting the origin request
	 * @param HttpRequest $target The target instance
	 * @return HttpRequest
	 */
	static public function Origin(HttpRequest &$target = null) {
		$props = [
			'protocol' => self::protocol(),
			'name'     => self::hostName(),
			'mime'     => self::mime(),
			'encoding' => self::encoding(),
			'method'   => self::method(),
			
			'mimes'    => self::acceptMimes(),
			'langs'    => self::acceptLanguages()
		];
		
		
		if (is_null($target)) $target = new HttpRequest($props);
		else $target->_construct($props);
		
		return $target;
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
	static public function protocol() {
		switch (filter_input(INPUT_SERVER, 'HTTPS')) {
			case '' :
			case 'off' :
				return 'http';
			default : return 'https';
		}
	}
	
	/**
	 * Returns the origin request host name
	 * @return string
	 */
	static public function hostName() {
		return filter_input(INPUT_SERVER, 'SERVER_NAME');
	}
	
	
	/**
	 * Returns the origin request ip address
	 * @return string
	 */
	static public function clientIP() {
		return filter_input(INPUT_SERVER, 'REMOTE_ADDR');
	}
	
	/**
	 * Returns the origin request http method
	 * @return string
	 */
	static public function method() {
		return filter_input(INPUT_SERVER, 'REQUEST_METHOD');
	}
	
	/**
	 * Returns the origin request accepted mime types
	 * @return string[]
	 */
	static public function acceptMimes() {
		if (empty(self::$_mimes)) self::$_mimes = self::_parseAccept(filter_input(INPUT_SERVER, 'HTTP_ACCEPT'), '[a-z]+\\/[a-z]+|\\*\\/\\*');
			
		return self::$_mimes;
	}
	
	/**
	 * Returns the origin request body mime type
	 * @return string
	 */
	static public function mime() {
		$mime = str_replace(';', '&', filter_input(INPUT_SERVER, 'CONTENT_TYPE'));
		
		$data = [];
		
		if (!empty($mime)) parse_str('mime=' . $mime, $data);
		
		return array_key_exists('mime', $data) ? $data['mime'] : '';
	}
	
	/**
	 * Returns the origin request body charset
	 * @return string
	 */
	static public function encoding() {
		$mime = str_replace(';', '&', filter_input(INPUT_SERVER, 'CONTENT_TYPE'));
		
		$data = [];
		
		if (!empty($mime)) parse_str('mime=' . $mime, $data);
		
		return array_key_exists('charset', $data) ? $data['encoding'] : '';
	}
	
	/**
	 * Returns the origin request accepted languages
	 * @return string[]
	 */
	static public function acceptLanguages() {
		if (empty(self::$_languages)) self::$_languages = self::_parseAccept(filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE'), '[A-Za-z]{2}(?:-[A-Za-z]{2})?');
		
		return self::$_languages;
	}
	
	/**
	 * Returns the origin request query
	 * @return string[]
	 */
	static public function query() {
		$res = [];
		
		parse_str(filter_input(INPUT_SERVER, 'QUERY_STRING'), $res);
		
		return $res;
	}
	
	/**
	 * Returns the origin request body
	 * @return string
	 */
	static public function body() {
		$handle = fopen('php://input', 'r');
		
		return stream_get_contents($handle);
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
	 * Returns <code>true</code> if the origin request is a <em>XMLHttpRequest</em>, <code>false</code> otherwise
	 * @return bool
	 */
	static public function isXMLHttp() {
		return filter_input(INPUT_SERVER, 'HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest';
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
		return array_key_exists('time', $this->_property) ? $this->_property['time'] : self::time();
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
		return array_key_exists('protocol', $this->_property) ? $this->_property['protocol'] : self::protocol();
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
		return array_key_exists('name', $this->_property) ? $this->_property['name'] : self::hostName();
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
		return array_key_exists('method', $this->_property) ? $this->_property['method'] : self::method();
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
		return array_key_exists('mime', $this->_property) ? $this->_property['mime'] : self::mime();
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
		return array_key_exists('encoding', $this->_property) ? $this->_property['encoding'] : self::charset();
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
		return array_key_exists('mimes', $this->_property) ? $this->_property['mimes'] : self::acceptMimes();
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
		return array_key_exists('langs', $this->_property) ? $this->_property['langs'] : self::acceptLanguages();
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
	
	
	public function getQuery() {
		return array_key_exists('query', $this->_property) ? $this->_property['query'] : self::query();
	}
	
	public function setQuery() {
		//IMPLEMENT
	}
	
	
	/**
	 * Returns the raw request body of the instance
	 * @return string
	 */
	public function getBody() {
		return array_key_exists('body', $this->_property) ? $this->_property['body'] : self::body();
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
	
	
	/**
	 * Returns <code>true</code> if the instance contains all set properties of <code>$request</code>, <code>false</code> otherwise
	 * @param HttpRequest $request The contained request
	 * @return bool
	 */
	public function contains(HttpRequest $request) {
		//IMPLEMENT
	}
	
	/**
	 * Returns <code>true</code> if the instance contains all properties represented by <code>$request</code>, <code>false</code> otherwise
	 * @param array $request The request properties
	 * @return bool
	 */
	public function containsArray(Array $request) {
		//IMPLEMENT
	}
}