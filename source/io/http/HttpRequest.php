<?php

namespace lola\io\http;

use lola\io\http\IHttpDriver;
use lola\io\http\IHttpRequest;

use lola\io\IRequest;
use lola\io\IReply;
use lola\io\IClient;
use lola\io\mime\IMimeContainer;
use lola\io\mime\IMimePayload;
use lola\io\http\IHttpCookies;



class HttpRequest
implements IHttpRequest
{

	const VERSION = '0.6.1';



	private $_driver;
	private $_source;
	private $_rules;

	private $_properties;
	private $_headers;
	private $_body;


	public function __construct(IHttpDriver& $driver) {
		$this->_driver =& $driver;
		$this->_source = $driver->useRequestResource();
		$this->_rules = $driver->useConfig();

		$this->_properties = [];
		$this->_headers = [];
		$this->_body = null;
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
		if (!array_key_exists('time', $this->_properties)) $this->_properties['time'] = $this->_source->getTime();

		return $this->_properties['time'];
	}

	public function setTime(int $time) : IRequest {
		if ($time < 0) throw new \ErrorException();

		$this->_properties['time'] = $time;

		return $this;
	}


	public function getProtocol() : string {
		if (!array_key_exists('protocol', $this->_properties)) $this->_properties['protocol'] = $this->_source->getProtocol();

		return $this->_properties['protocol'];
	}

	public function setProtocol(string $protocol) : IRequest {
		if (!$this->_rules->isProtocol($protocol)) throw new \ErrorException();

		$this->_properties['protocol'] = $protocol;

		return $this;
	}


	public function getHostName() : string {
		if (!array_key_exists('host', $this->_properties)) $this->_properties['host'] = $this->_source->getHostName();

		return $this->_properties['host'];
	}

	public function setHostName(string $hostName) : IRequest {
		if (empty($hostName)) throw new \ErrorException();

		$this->_properties['host'] = $hostName;

		return $this;
	}


	public function getPath() : string {
		if (!array_key_exists('path', $this->_properties)) $this->_properties['path'] = $this->_source->getPath();

		return $this->_properties['path'];
	}

	public function setPath(string $path) : IRequest {
		$this->_properties['path'] = $path;

		return $this;
	}


	public function& useQuery() : array {
		if (!array_key_exists('query', $this->_properties)) $this->_properties['query'] = $this->_source->getQuery();

		return $this->_properties['query'];
	}

	public function setQuery(array $query) : IRequest {
		foreach ($query as $key => $value) {
			if (!is_string($value)) throw new \ErrorException();
		}

		$this->_properties['query'] = $query;

		return $this;
	}


	public function getMethod() : string {
		if (!array_key_exists('method', $this->_properties)) $this->_properties['method'] = $this->_source->getMethod();

		return $this->_properties['method'];
	}

	public function setMethod(string $method) : IHttpRequest {
		if (!$this->_rules->isMethod($method)) throw new \ErrorException();

		$this->_properties['method'] = $method;

		return $this;
	}


	public function getMime() : string {
		if (!array_key_exists('mime', $this->_properties)) $this->_properties['mime'] = $this->_source->getMime();

		return $this->_properties['mime'];
	}

	public function setMime(string $mime) : IMimeContainer {
		if (!$this->_rules->isMime($mime)) throw new \ErrorException();

		$this->_properties['mime'] = $mime;

		return $this;
	}


	public function getEncoding() : string {
		if (!array_key_exists('encoding', $this->_properties)) $this->_properties['encoding'] = $this->_source->getEncoding();

		return $this->_properties['encoding'];
	}

	public function setEncoding(string $encoding) : IMimeContainer {
		if (!$this->_rules->isEncoding($encoding)) throw new \ErrorException();

		$this->_properties['encoding'] = $encoding;

		return $this;
	}


	public function& useAcceptMimes() : array {
		if (!array_key_exists('acceptMimes', $this->_properties)) $this->_properties['acceptMimes'] = $this->_source->getAcceptMimes();

		return $this->_properties['acceptMimes'];
	}

	public function getPreferedAcceptMime(array $mimes) : string {
		$accept = $this->useAcceptMimes();

		foreach ($accept as $mime => $score) {
			if (in_array($mime, $mimes)) return $mime;
		}

		return '';
	}

	public function setAcceptMimes(array $mimes) : IHttpRequest {
		foreach ($mimes as $mime => $score) {
			if (
				!$this->_rules->isMime($mime) ||
				!is_float($score) || $score < 0.0 || $score > 1.0
			) throw new \ErrorException();
		}

		$this->_properties['acceptMimes'] = $mimes;

		return $this;
	}


	public function& useAcceptLanguages() : array {
		if (!array_key_exists('acceptLanguages', $this->_properties)) $this->_properties['acceptLanguages'] = $this->_source->getAcceptLanguages();

		return $this->_properties['acceptLanguages'];
	}

	public function getPreferedAcceptLanguage(array $langs) : string {
		$accept = $this->useAcceptLanguages();

		foreach ($accept as $lang => $score) {
			if (in_array($lang, $langs)) return $lang;
		}

		return '';
	}

	public function setAcceptLanguages(array $langs) : IHttpRequest {
		foreach ($langs as $score) {
			if (!is_float($score) || $score < 0.0 || $score > 1.0) throw new \ErrorException();
		}

		$this->_properties['acceptLanguages'] = $langs;

		return $this;
	}


	public function hasHeader(string $name) : bool {
		if (empty($name)) throw new \ErrorException();

		return array_key_exists($name, $this->_headers) || $this->_source->hasHeader($name);
	}

	public function getHeader(string $name) : string {
		if (empty($name)) throw new \ErrorException();

		switch ($name) {
			case 'Content-Type' : return $this->_rules->buildHeader($this->getMime(), [ 'charset' => $this->getEncoding() ]);
			case 'Accept' : return $this->_rules->buildWeightedHeader($this->useAcceptMimes());
			case 'Accept-Language' : return $this->_rules->buildWeightedHeader($this->useAcceptLanguages());
		}

		if (!array_key_exists($name, $this->_headers)) $this->_headers[$name] = $this->_source->getHeader($name);

		return $this->_headers[$name];
	}

	public function setHeader(string $name, string $value) : IHttpRequest {
		if (empty($name)) throw new \ErrorException();

		if ($name === 'Content-Type') {
			$params = $this->_rules->parseHeader($value);

			$this->_properties['mime'] = $params[IHttpConfig::HEADER_PARAM_DEFAULT];
			$this->_properties['encoding'] = $params['charset'];
		}
		else if ($name === 'Accept') $this->_properties['acceptMimes'] = $this->_rules->parseWeightedHeader($value);
		else if ($name === 'Accept-Language') $this->_properties['acceptLanguages'] = $this->_rules->parseWeightedHeader($value);
		else $this->_headers[$name] = $value;

		return $this;
	}


	public function getBody() : string {
		if (is_null($this->_body)) $this->_body = $this->_source->getBody();

		return $this->_body;
	}

	public function setBody(string $body) : IMimeContainer {
		$this->_body = $body;

		return $this;
	}
}
