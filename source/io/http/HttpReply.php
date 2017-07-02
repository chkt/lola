<?php

namespace lola\io\http;

use lola\io\IRequest;
use lola\io\mime\IMimeContainer;
use lola\io\mime\IMimePayload;



class HttpReply
implements IHttpReply
{

	private $_driver;
	private $_rules;

	private $_message;


	public function __construct(IHttpDriver& $driver) {
		$this->_driver = $driver;
		$this->_rules = $driver->useConfig();

		$this->_message = $driver->useReplyMessage();
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
		return explode(' ', $this->_message->getStartLine(), 3)[1];
	}

	public function setCode(string $code) : IHttpReply {
		if (!$this->_rules->isCode($code)) throw new \ErrorException();

		$this->_message->setStartLine($this->_rules->getCodeHeader($code));

		return $this;
	}

	public function getCodeHeader() : string {
		return $this->_message->getStartLine();
	}

	public function getCodeMessage() : string {
		return $this->_driver
			->useConfig()
			->getCodeMessage($this->getCode());
	}


	public function getMime() : string {
		return $this->_message->hasHeader(IHttpMessage::HEADER_CONTENT_TYPE) ?
			HttpConfig::parseHeader($this->_message->getHeader(IHttpMessage::HEADER_CONTENT_TYPE))[IHttpConfig::HEADER_PARAM_DEFAULT] :
			'';
	}

	public function setMime(string $mime) : IMimeContainer {
		if (!$this->_rules->isMime($mime)) throw new \ErrorException();

		$this->_message->setHeader(
			IHttpMessage::HEADER_CONTENT_TYPE,
			HttpConfig::injectHeader($this->_message->getHeader(IHttpMessage::HEADER_CONTENT_TYPE), [IHttpConfig::HEADER_PARAM_DEFAULT => $mime])
		);

		return $this;
	}


	public function getEncoding() : string {
		return $this->_message->hasHeader(IHttpMessage::HEADER_CONTENT_TYPE) ?
			HttpConfig::parseHeader($this->_message->getHeader(IHttpMessage::HEADER_CONTENT_TYPE))['charset'] :
			'';
	}

	public function setEncoding(string $encoding) : IMimeContainer {
		if (!$this->_rules->isEncoding($encoding)) throw new \ErrorException();

		$this->_message->setHeader(
			IHttpMessage::HEADER_CONTENT_TYPE,
			HttpConfig::injectHeader($this->_message->getHeader(IHttpMessage::HEADER_CONTENT_TYPE), ['charset' => $encoding])
		);

		return $this;
	}


	public function isRedirect() : bool {
		return $this->_rules->isRedirectCode($this->getCode());
	}

	public function getRedirectTarget() : string {
		return $this->_message->hasHeader(IHttpMessage::HEADER_LOCATION) ?
			$this->_message->getHeader(IHttpMessage::HEADER_LOCATION) :
			'';
	}

	public function setRedirectTarget(string $url) : IHttpReply {
		$this->_message->setHeader(IHttpMessage::HEADER_LOCATION, $url);

		return $this;
	}


	public function hasHeader(string $name) : bool {
		return $this->_message->hasHeader($name);
	}

	public function getHeader(string $name) : string {
		return $this->_message->getHeader($name);
	}

	public function setHeader(string $name, string $value) : IHttpReply {
		$this->_message->setHeader($name, $value);

		return $this;
	}

	public function resetHeader(string $name) : IHttpReply {
		$this->_message->clearHeader($name);

		return $this;
	}


	public function getBody() : string {
		return $this->_message->getBody();
	}

	public function setBody(string $body) : IMimeContainer {
		$this->_message->setBody($body);

		return $this;
	}


	public function send() {
		return $this->_driver->sendReply();
	}
}
