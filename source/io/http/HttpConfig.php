<?php

namespace lola\io\http;

use lola\io\http\IHttpConfig;



class HttpConfig
implements IHttpConfig
{

	const VERSION = '0.5.0';


	static public function parseHeader(string $header) : array {
		$data = [];

		parse_str(self::HEADER_PARAM_DEFAULT . '=' . str_replace(';', '&', $header), $data);

		return $data;
	}

	static public function buildHeader(string $default, array $params) : string {
		$res = [];

		foreach ($params as $name => $value) $res[] = $name . '=' . $value;

		return $default . ';' . implode(';', $res);
	}

	static public function parseWeightedHeader(string $header) : array {
		$items = explode(',', $header);
		$res = [];

		foreach ($items as $item) {
			$match = [];

			if (!preg_match('/^([^;]+)(?:;q=(0\\.\\d+))?$/', $item, $match)) throw new \ErrorException();

			$res[$match[1]] = count($match) === 3 ? (float) $match[2] : 1.0;
		}

		arsort($res, SORT_NUMERIC);

		return $res;
	}

	static public function buildWeightedHeader(array $params) : string {
		$res = [];

		foreach ($params as $name => $score) $res[] = $name . ($score !== 1.0 ? ';q=' . (string) $score : '');

		return implode(',', $res);
	}



	private $_rules;
	private $_links;


	public function __construct() {
		$this->_rules = [
			self::RULE_PROTOCOL => [
				self::PROTOCOL_HTTP,
				self::PROTOCOL_HTTPS
			],
			self::RULE_METHOD => [
				self::METHOD_GET,
				self::METHOD_POST,
				self::METHOD_PUT,
				self::METHOD_PATCH,
				self::METHOD_DELETE,
				self::METHOD_HEAD,
				self::METHOD_OPTIONS
			],
			self::RULE_MIME => [
				self::MIME_PLAIN,
				self::MIME_HTML,
				self::MIME_XML,
				self::MIME_XHTML,
				self::MIME_JSON,
				self::MIME_FORM
			],
			self::RULE_ENCODING => [
				self::ENCODING_UTF8
			],
			self::RULE_CODE => [
				self::CODE_OK,
				self::CODE_NO_CONTENT,
				self::CODE_MOVED_PERMANENT,
				self::CODE_FOUND,
				self::CODE_REDIRECT,
				self::CODE_MOVED_TEMPORARY,
				self::CODE_NOT_VALID,
				self::CODE_NOT_AUTH,
				self::CODE_NOT_FOUND,
				self::CODE_GONE,
				self::CODE_ERROR,
				self::CODE_UNAVAILABLE
			],
			self::RULE_REDIRECT_CODE => [
				self::CODE_MOVED_PERMANENT,
				self::CODE_FOUND,
				self::CODE_REDIRECT,
				self::CODE_MOVED_TEMPORARY
			]
		];

		$this->_links = [
			self::LINK_CODE_HEADER => [
				self::CODE_OK => 'HTTP/1.1 200 OK',
				self::CODE_NO_CONTENT => 'HTTP/1.1 204 No Content',
				self::CODE_MOVED_PERMANENT => 'HTTP/1.1 301 Moved Permanently',
				self::CODE_FOUND => 'HTTP/1.1 302 Found',
				self::CODE_REDIRECT => 'HTTP/1.1 303 See Other',
				self::CODE_MOVED_TEMPORARY => 'HTTP/1.1 307 Temporary Redirect',
				self::CODE_NOT_VALID => 'HTTP/1.1 400 Bad Request',
				self::CODE_NOT_AUTH => 'HTTP/1.1 403 Forbidden',
				self::CODE_NOT_FOUND => 'HTTP/1.1 404 Not Found',
				self::CODE_GONE => 'HTTP/1.1 410 Gone',
				self::CODE_ERROR => 'HTTP/1.1 500 Internal Server Error',
				self::CODE_UNAVAILABLE => 'HTTP/1.1 503 Service Unavailable'
			],
			self::LINK_CODE_MESSAGE => [
				self::CODE_OK => '200 - OK',
				self::CODE_NO_CONTENT => '204 - No Content',
				self::CODE_MOVED_PERMANENT => '301 - Moved Permanently',
				self::CODE_FOUND => '302 - Found',
				self::CODE_REDIRECT => '303 - See Other',
				self::CODE_MOVED_TEMPORARY => '307 - Temporary Redirect',
				self::CODE_NOT_VALID => '400 - Bad Request',
				self::CODE_NOT_AUTH => '403 - Forbidden',
				self::CODE_NOT_FOUND => '404 - Page not found',
				self::CODE_GONE => '410 - Gone',
				self::CODE_ERROR => '500 - Internal Server Error',
				self::CODE_UNAVAILABLE => '503 - Service Unavailable'
			],
			self::LINK_MIME_BODY => [
				self::MIME_PLAIN => '%m%l',
				self::MIME_HTML => '<!DOCTYPE html><html><head><title>%m</title></head><body><p>%m%l</p></body></html>',
			],
			self::LINK_MIME_LINK => [
				self::MIME_PLAIN => ': %m',
				self::MIME_HTML => ': <a href="%m">%m</a>'
			]
		];
	}


	private function _isRule($rule) : bool {
		return in_array($rule, [
			self::RULE_PROTOCOL,
			self::RULE_METHOD,
			self::RULE_MIME,
			self::RULE_ENCODING,
			self::RULE_CODE,
			self::RULE_REDIRECT_CODE
		]);
	}

	private function _isLink($link) : bool {
		return in_array($link, [
			self::LINK_CODE_HEADER,
			self::LINK_CODE_MESSAGE,
			self::LINK_MIME_BODY,
			self::LINK_MIME_LINK
		]);
	}

	private function _buildString(string $type, string $key, string $content, string $default = '') : string {
		$map = $this->_links[$type];

		$tmpl = array_key_exists($key, $map) ? $map[$key] : $default;

		return str_replace('%m', $content, $tmpl);
	}


	public function isProtocol($protocol) : bool {
		return in_array($protocol, $this->_rules[self::RULE_PROTOCOL]);
	}

	public function isMethod($method) : bool {
		return in_array($method, $this->_rules[self::RULE_METHOD]);
	}

	public function isMime($mime) : bool {
		return in_array($mime, $this->_rules[self::RULE_MIME]);
	}

	public function isEncoding($encoding) : bool {
		return in_array($encoding, $this->_rules[self::RULE_ENCODING]);
	}

	public function isCode($code) : bool {
		return in_array($code, $this->_rules[self::RULE_CODE]);
	}

	public function isRedirectCode($code) : bool {
		return in_array($code, $this->_rules[self::RULE_REDIRECT_CODE]);
	}


	public function getCodeHeader(string $code) : string {
		$map = $this->_links[self::LINK_CODE_HEADER];

		if (!array_key_exists($code, $map)) throw new \ErrorException();

		return $map[$code];
	}

	public function getCodeMessage(string $code) : string {
		$map = $this->_links[self::LINK_CODE_MESSAGE];

		return array_key_exists($code, $map) ? $map[$code] : '';
	}

	public function getMimeBody(string $code, string $mime, string $target = null) : string {
		$message = $this->getCodeMessage($code);

		$body = $this->_buildString(self::LINK_MIME_BODY, $mime, $message, '%m');

		if ($this->isRedirectCode($code ) && !is_null($target)) $link = $this->_buildString(self::LINK_MIME_LINK, $mime, $target);
		else $link = '';

		return str_replace('%l', $link, $body);
	}


	public function hasRule(string $type, string $rule) : bool {
		if (!$this->_isRule($type) || empty($rule)) throw new \ErrorException();

		return in_array($rule, $this->_rules[$type]);
	}

	public function addRule(string $type, string ...$rules) : IHttpConfig {
		if (!$this->_isRule($type)) throw new \ErrorException();

		$items = $this->_rules[$type];

		foreach ($rules as $rule) {
			if (empty($rule)) throw new \ErrorException();

			if (!in_array($rule, $items)) $items[] = $rule;
		}

		$this->_rules[$type] = $items;

		return $this;
	}

	public function removeRule(string $type, string ...$rules) : IHttpConfig {
		if (!$this->_isRule($type)) throw new \ErrorException();

		$items = $this->_rules[$type];

		foreach ($rules as $rule) {
			if (empty($rule)) throw new \ErrorException();

			$index = array_search($rule, $items);

			if ($index !== false) array_splice($items, $index, 1);
		}

		$this->_rules[$type] = $items;

		return $this;
	}


	public function hasLink(string $type, string $key) : bool {
		if (!$this->_isLink($type)) throw new \ErrorException();

		return array_key_exists($key, $this->_links[$type]);
	}

	public function addLink(string $type, string $key, string $value) : IHttpConfig {
		if (!$this->_isLink($type) || empty($key)) throw new \ErrorException();

		$this->_links[$type][$key] = $value;

		return $this;
	}

	public function removeLink(string $type, string $key) : IHttpConfig {
		if (!$this->_isLink($type)) throw new \ErrorException();

		unset($this->_links[$type][$key]);

		return $this;
	}
}
