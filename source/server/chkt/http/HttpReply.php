<?php

namespace chkt\http;

use chkt\type\Dictionary;
use chkt\http\Cookie;



class HttpReply {
	
	const VERSION = '0.0.6';
	
	const MIME_PLAIN = 'text/plain';
	const MIME_HTML  = 'text/html';
	const MIME_XML   = 'application/xml';
	const MIME_JSON  = 'application/json';
	
	const ENCODING_UTF8  = 'utf-8';
	
	
	
	static private $_CODE = ['200','204','301','302','303','307','400','403','404','500','503'];
	static private $_INVALID = ['400','403','404'];
	static private $_REDIRECT = ['301','302','303','307'];
	static private $_MESSAGE = [
		'200' => '200 - OK',
		'204' => '204 - No Content',
		'301' => '301 - Moved Permanently',			//Permanent redirect - body SHOULD contain link if method != HEAD; redirect with original method; ua SHOULD confirm if method != HEAD,GET
		'302' => '302 - Found',						//Temporary redirect - body SHOULD contain link if method != HEAD; redirect with original method; ua SHOULD confirm if method != HEAD,GET
		'303' => '303 - See other',					//Temporary redirect - body SHOULD contain link if method != HEAD; redirect with GET
		'307' => '307 - Temporary Redirect',			//Temporary redirect - body SHOULD contain link if method != HEAD; redirect with original method; ua SHOULD confirm if method != HEAD,GET
		'400' => '400 - Bad Request',
		'403' => '403 - Forbidden',
		'404' => '404 - Page not found',
		'500' => '500 - Internal Server error',
		'503' => '503 - Service Unavailable'
	];
	static private $_HEADER = [
		'200' => 'HTTP/1.1 200 OK',
		'204' => 'HTTP/1.1 204 No Content',
		'301' => 'HTTP/1.1 301 Moved Permanently',
		'302' => 'HTTP/1.1 302 Found',
		'303' => 'HTTP/1.1 303 See other',
		'307' => 'HTTP/1.1 307 Temporary Redirect',
		'400' => 'HTTP/1.1 400 Bad Request',
		'403' => 'HTTP/1.1 403 Forbidden',
		'404' => 'HTTP/1.1 404 Not Found',
		'500' => 'HTTP/1.1 500 Internal Server Error',
		'503' => 'HTTP/1.1 503 Service Unavailable'
	];
	
	static private $_MIME = [
		self::MIME_PLAIN,
		self::MIME_HTML,
		self::MIME_XML,
		self::MIME_JSON
	];
	
	static private $_ENCODING = [
		self::ENCODING_UTF8
	];
	
	
	
	private $_code   = '';
	private $_mime   = '';
	private $_char   = '';
	
	private $_target = '';
	
	protected $_header  = null;
	protected $_cookie  = null;
	protected $_content = '';
	
	
	
	static public function Reply($code = 200,  $message = '', $mime = self::MIME_PLAIN, $encoding = self::ENCODING_UTF8) {
		if (
			static::isHttpRedirectCode((string) $code) ||
			!is_string($message)
		) throw new ErrorException();
		
		$ins = new static($code, $mime, $encoding);
		$ins->_content = $message;
		$ins->send();
	}
	
	static public function ReplyRedirect($code, $location, $mime = self::MIME_PLAIN, $encoding = self::ENCODING_UTF8) {
		if (
			!static::isHttpRedirectCode((string) $code) ||
			!is_string($location) || empty($location)
		) throw new ErrorException();
		
		$ins = new static($code, $mime, $encoding);
		$ins->_target = $location;
		$ins->send();
	}
	
	static public function ReplyOB($code = 200, $mime = self::MIME_PLAIN, $encoding = self::ENCODING_UTF8) {
		$ins = new static($code, $mime, $encoding);
		$ins->sendOB();
	}
	
	
	static public function String($code, $string, $mime = self::MIME_PLAIN, $encoding = self::ENCODING_UTF8, HttpReply &$target = null) {
		if (!is_null($target)) $target->__construct($code, $mime, $encoding);
		else $target = new static($code, $mime, $encoding);
		
		$target->setContent($string);
		
		return $target;
	}
	
	static public function OB($code = 200, $mime = self::MIME_PLAIN, $encoding = self::ENCODING_UTF8, HttpReply &$target = null) {
		if (ob_get_level() === 0) throw new ErrorException();
		
		if (!is_null($target)) $target->__construct($code, $mime, $encoding);
		else $target = new static($code, $mime, $encoding);
		
		$target->setContentOB();
		
		return $target;
	}
	
	
	static public function isHttpCode($code) {
		return in_array($code, self::$_CODE);
	}
	
	static public function isHttpRedirectCode($code) {
		return in_array($code, self::$_REDIRECT);
	}
	
	static public function isMimeType($mime) {	
		return in_array($mime, self::$_MIME);
	}
	
	static public function isEncoding($encoding) {
		return in_array($encoding, self::$_ENCODING);
	}
	
	
	static public function messageOfCode($code) {
		return array_key_exists($code, self::$_MESSAGE) ? self::$_MESSAGE[$code] : '';
	}
	
	
	
	public function __construct($code = 200, $mime = self::MIME_PLAIN, $encoding = self::ENCODING_UTF8) {
		if (
			!static::isHttpCode((string) $code) ||
			!static::isMimeType($mime) ||
			!static::isEncoding($encoding)
		) throw new ErrorException();
		
		$this->_code   = $code;
		$this->_mime   = $mime;
		$this->_char   = $encoding;
		
		$this->_target = '';
		
		$this->_header  = new Dictionary('string');
		$this->_cookie  = null;
		$this->_content = '';
	}
	
	
	public function getCode() {
		return $this->_code;
	}
	
	public function setCode($code) {
		if (!static::isHttpCode((string) $code)) throw new ErrorException();
		
		$this->_code = $code;
		
		return $this;
	}
	
	
	public function getCodeMessage() {
		return self::$_MESSAGE[$this->_code];
	}
	
	
	public function getMime() {
		return $this->_mime;
	}
	
	public function setMime($mime) {
		if (!static::isMimeType($mime)) throw new ErrorException();
		
		$this->_mime = $mime;
		
		return $this;
	}
	
	
	public function getEncoding() {
		return $this->_char;
	}
	
	public function setEncoding($encoding) {
		if (!static::isEncoding($encoding)) throw new ErrorException();
		
		$this->_char = $encoding;
		
		return $this;
	}
	
	
	public function isInvalid() {
		return in_array((string) $this->_code, self::$_INVALID);
	}
	
	
	public function isRedirect() {
		return in_array((string) $this->_code, self::$_REDIRECT);
	}
	
	public function getRedirectTarget() {
		return in_array($this->_code, self::$_REDIRECT) ? $this->_target : '';
	}
	
	public function setRedirectTarget($url) {
		if (!is_string($url)) throw new ErrorException();
		
		$this->_target = in_array($this->_code, self::$_REDIRECT) ? $url : '';
		
		return $this;
	}
	
	
	public function getHeaders() {
		return $this->_header;
	}
	
	public function &useCookies() {
		if (is_null($this->_cookie)) $this->_cookie = new Cookie();
		
		return $this->_cookie;
	}
	
	
	public function getContent() {
		return $this->_content;
	}
	
	public function setContent($string) {
		if (!is_string($string)) throw new ErrorException();
		
		$this->_content = $string;
		
		return $this;
	}
	
	public function setContentOB() {
		if (ob_get_level() === 0) throw new ErrorException();
		
		$ob = ob_get_contents();
		
		if ($ob === false) $this->_content = '';
		else $this->_content = $ob;
		
		return $this;
	}
	
	
	public function send() {
		$code    = (string) $this->_code;
		$mime    = $this->_mime;
		$char    = $this->_char;
		$content = $this->_content;
		
		while (ob_get_level() !== 0) ob_end_clean();
		
		header(self::$_HEADER[$code]);
		header('Content-Type: ' . $mime . '; charset=' . $char);
		
		$keys = ['Content-Type', 'Content-Length', 'Set-Cookie'];
				
		if ($this->isRedirect() && !empty($this->_target)) {
			header('Location:' . $this->_target);
			
			$keys[] = 'Location';
			
			if ($mime === self::MIME_HTML) $content = '<html><head><title>' . self::$_MESSAGE[$code] . '</title></head><body><p>' . self::$_MESSAGE[$code] . '<a href="' . $this->_target . '">' . $this->_target . '</a></p></body></html>';
		}
		else if (empty($this->_content)) $content = self::$_MESSAGE[$code];
		
		header('Content-Length: ' . strlen($content));		
		
		$this->_header->removeItems($keys);
		
		foreach ($this->_header->getAllItems() as $key => $value) header("$key: $value");
		
		if (!is_null($this->_cookie)) $this->_cookie->send();
		
		print $content;
		
		exit();
	}
	
	public function sendOB() {
		$this
			->setContentOB()
			->send();
	}
}