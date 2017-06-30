<?php

namespace lola\io\http;

use lola\io\IRequest;
use lola\io\mime\IMimeConfig;
use lola\io\mime\IMimeContainer;
use lola\io\mime\IMimePayload;



class HttpReply
implements IHttpReply
{

	private $_driver;
	private $_rules;

	private $_code;
	private $_mime;
	private $_encoding;

	private $_isRedirect;
	private $_redirectTarget;

	private $_headers;
	private $_body;


	public function __construct(IHttpDriver& $driver) {
		$this->_driver = $driver;
		$this->_rules = $driver->useConfig();

		$this->_code = IHttpConfig::CODE_OK;
		$this->_mime = IMimeConfig::MIME_PLAIN;
		$this->_encoding = IMimeConfig::ENCODING_UTF8;

		$this->_isRedirect = false;
		$this->_redirectTarget = '';

		$this->_headers = [];
		$this->_body = '';
	}


	public function& usePayload() : IMimePayload {
		return $this->_driver->useReplyPayload();
	}

	public function& useRequest() : IRequest {
		return $this->_driver->useRequest();
	}

	public function& useCookies() : IHttpCookies {
		return $this->_driver->useCookies();
	}


	public function getCode() : string {
		return $this->_code;
	}

	public function setCode(string $code) : IHttpReply {
		if (!$this->_rules->isCode($code)) throw new \ErrorException();

		$this->_code = $code;
		$this->_isRedirect = $this->_rules->isRedirectCode($code);

		return $this;
	}

	public function getCodeHeader() : string {
		return $this->_driver
			->useConfig()
			->getCodeHeader($this->_code);
	}

	public function getCodeMessage() : string {
		return $this->_driver
			->useConfig()
			->getCodeMessage($this->_code);
	}


	public function getMime() : string {
		return $this->_mime;
	}

	public function setMime(string $mime) : IMimeContainer {
		if (!$this->_rules->isMime($mime)) throw new \ErrorException();

		$this->_mime = $mime;

		return $this;
	}


	public function getEncoding() : string {
		return $this->_encoding;
	}

	public function setEncoding(string $encoding) : IMimeContainer {
		if (!$this->_rules->isEncoding($encoding)) throw new \ErrorException();

		$this->_encoding = $encoding;

		return $this;
	}


	public function isRedirect() : bool {
		return $this->_isRedirect;
	}

	public function getRedirectTarget() : string {
		return $this->_redirectTarget;
	}

	public function setRedirectTarget(string $url) : IHttpReply {
		$this->_redirectTarget = $url;

		return $this;
	}


	public function hasHeader(string $name) : bool {
		if (empty($name)) throw new \ErrorException();

		if ($name === 'Content-Type') return true;
		else if ($name === 'Location') return $this->_redirectTarget;
		else return array_key_exists($name, $this->_headers);
	}

	public function getHeader(string $name) : string {
		if (empty($name)) throw new \ErrorException();

		if ($name === 'Content-Type') return $this->_rules->buildHeader($this->_mime, [ 'charset' => $this->_encoding ]);
		else if ($name === 'Location') return $this->_redirectTarget;
		else return array_key_exists($name, $this->_headers) ? $this->_headers[$name] : '';
	}

	public function setHeader(string $name, string $value) : IHttpReply {
		if (empty($name)) throw new \ErrorException();

		if ($name === 'Content-Type') {
			$attrs = $this->_rules->parseHeader($value);

			$this
				->setMime($attrs[IHttpConfig::HEADER_PARAM_DEFAULT])
				->setEncoding($attrs['charset']);
		}
		else if ($name === 'Location') $this->_redirectTarget = $value;
		else $this->_headers[$name] = $value;

		return $this;
	}

	public function resetHeader(string $name) : IHttpReply {
		if (empty($name)) throw new \ErrorException();

		if ($name === 'Content-Type') {
			$this->_mime = IMimeConfig::MIME_PLAIN;
			$this->_encoding = IMimeConfig::ENCODING_UTF8;
		}
		else if ($name === 'Location') $this->_redirectTarget = '';
		else unset($this->_headers[$name]);

		return $this;
	}

	public function getHeaders() : array {
		return $this->_headers;
	}


	public function getBody() : string {
		return $this->_body;
	}

	public function setBody(string $body) : IMimeContainer {
		$this->_body = $body;

		return $this;
	}


	public function send() {
		return $this->_driver->sendReply();
	}
}
