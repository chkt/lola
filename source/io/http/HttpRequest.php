<?php

namespace lola\io\http;

use lola\io\IRequest;
use lola\io\IReply;
use lola\io\IClient;
use lola\io\connect\IConnection;
use lola\io\mime\IMimeContainer;
use lola\io\mime\IMimePayload;



class HttpRequest
implements IHttpRequest
{

	private $_driver;
	private $_rules;

	private $_connection;
	private $_message;


	public function __construct(IHttpDriver& $driver) {
		$this->_driver =& $driver;
		$this->_rules = $driver->useConfig();

		$this->_connection = $driver->useConnection();
		$this->_message = $driver->useRequestMessage();
	}


	public function& usePayload() : IMimePayload {
		return $this->_driver->useRequestPayload();
	}

	public function& useReply() : IReply {
		return $this->_driver->useReply();
	}

	public function& useCookies() : IHttpCookies {
		return $this->_driver->useCookies();
	}

	public function& useClient() : IClient {
		return $this->_driver->useClient();
	}


	public function getTime() : int {
		return $this->_connection->getInt(IConnection::CONNECTION_TIME);
	}

	public function setTime(int $time) : IRequest {
		if ($time < 0) throw new \ErrorException();

		$this->_connection->setInt(IConnection::CONNECTION_TIME, $time);

		return $this;
	}


	public function getTLS() : bool {
		return $this->_connection->getBool(IConnection::CONNECTION_TLS);
	}

	public function setTLS(bool $tls) : IRequest {
		$this->_connection->setBool(IConnection::CONNECTION_TLS, $tls);

		return $this;
	}


	public function getHostName() : string {
		return $this->_connection->getString(IConnection::HOST_NAME);
	}

	public function setHostName(string $hostName) : IRequest {
		$this->_connection->setString(IConnection::HOST_NAME, $hostName);

		return $this;
	}


	public function getPath() : string {
		return parse_url(explode(' ', $this->_message->getStartLine(), 3)[1], PHP_URL_PATH);
	}

	public function setPath(string $path) : IRequest {
		$line = explode(' ', $this->_message->getStartLine(), 3);
		$uri = explode('?', $line[1], 2);
		$uri[0] = $path;
		$line[1] = implode('?', $uri);

		$this->_message->setStartLine(implode(' ', $line));

		return $this;
	}


	public function getQuery() : array {
		$res = [];

		parse_str(parse_url(explode(' ', $this->_message->getStartLine(), 3)[1], PHP_URL_QUERY), $res);

		return $res;
	}

	public function setQuery(array $query) : IRequest {
		$line = explode(' ', $this->_message->getStartLine(), 3);
		$uri = explode('?', $line[1], 2);
		$uri[1] = http_build_query($query, '', '&', PHP_QUERY_RFC3986);
		$line[1] = implode('?', $uri);

		$this->_message->setStartLine(implode(' ', $line));

		return $this;
	}


	public function getMethod() : string {
		return explode(' ', $this->_message->getStartLine(), 3)[0];
	}

	public function setMethod(string $method) : IHttpRequest {
		if (!$this->_rules->isMethod($method)) throw new \ErrorException();

		$line = explode(' ', $this->_message->getStartLine(), 3);
		$line[0] = $method;

		$this->_message->setStartLine(implode(' ', $line));

		return $this;
	}


	public function getMime() : string {
		return HttpConfig::parseHeader($this->_message->getHeader(IHttpMessage::HEADER_CONTENT_TYPE))[IHttpConfig::HEADER_PARAM_DEFAULT];
	}

	public function setMime(string $mime) : IMimeContainer {
		if (!$this->_rules->isMime($mime)) throw new \ErrorException();

		$this->_message->setHeader(
			IHttpMessage::HEADER_CONTENT_TYPE,
			HttpConfig::injectHeader($this->_message->getHeader(IHttpMessage::HEADER_CONTENT_TYPE), [ IHttpConfig::HEADER_PARAM_DEFAULT => $mime ]));

		return $this;
	}


	public function getEncoding() : string {
		$props = HttpConfig::parseHeader($this->_message->getHeader(IHttpMessage::HEADER_CONTENT_TYPE));

		return array_key_exists('charset', $props) ? $props['charset'] : '';
	}

	public function setEncoding(string $encoding) : IMimeContainer {
		if (!$this->_rules->isEncoding($encoding)) throw new \ErrorException();

		$this->_message->setHeader(
			IHttpMessage::HEADER_CONTENT_TYPE,
			HttpConfig::injectHeader($this->_message->getHeader(IHttpMessage::HEADER_CONTENT_TYPE), ['charset' => $encoding])
		);

		return $this;
	}


	public function getAcceptMimes() : array {
		return HttpConfig::parseWeightedHeader($this->_message->getHeader(IHttpMessage::HEADER_ACCEPT_MIME));
	}

	public function getPreferedAcceptMime(array $mimes) : string {
		$accept = $this->getAcceptMimes();

		foreach ($accept as $mime => $score) {
			if (in_array($mime, $mimes)) return $mime;
		}

		return '';
	}

	public function setAcceptMimes(array $mimes) : IHttpRequest {
		foreach ($mimes as $mime => $score) {
			if (!$this->_rules->isMime($mime)) throw new \ErrorException();
		}

		$this->_message->setHeader(IHttpMessage::HEADER_ACCEPT_MIME, HttpConfig::buildWeightedHeader($mimes));

		return $this;
	}


	public function getAcceptLanguages() : array {
		return HttpConfig::parseWeightedHeader($this->_message->getHeader(IHttpMessage::HEADER_ACCEPT_LANGUAGE));
	}

	public function getPreferedAcceptLanguage(array $langs) : string {
		$accept = $this->getAcceptLanguages();

		foreach ($accept as $lang => $score) {
			if (in_array($lang, $langs)) return $lang;
		}

		return '';
	}

	public function setAcceptLanguages(array $langs) : IHttpRequest {
		$this->_message->setHeader(IHttpMessage::HEADER_ACCEPT_LANGUAGE, HttpConfig::buildWeightedHeader($langs));

		return $this;
	}


	public function hasHeader(string $name) : bool {
		return $this->_message->hasHeader($name);
	}

	public function getHeader(string $name) : string {
		return $this->_message->getHeader($name);
	}

	public function setHeader(string $name, string $value) : IHttpRequest {
		$this->_message->setHeader($name, $value);

		return $this;
	}

	public function resetHeader(string $name) : IHttpRequest {
		$this->_message->resetHeader($name);

		return $this;
	}


	public function getBody() : string {
		return $this->_message->getBody();
	}

	public function setBody(string $body) : IMimeContainer {
		$this->_message->setBody($body);

		return $this;
	}
}
