<?php

namespace lola\io\http;



class HttpMessage
implements IHttpMessage
{

	private $_startLine;
	private $_headers;
	private $_body;


	public function __construct(string $startLine, array $headers = [], string $body = '') {
		$this->_startLine = $startLine;
		$this->_headers = $headers;
		$this->_body = $body;
	}


	public function getStartLine() : string {
		return $this->_startLine;
	}

	public function setStartLine(string $line) : IHttpMessage {
		$this->_startLine = $line;

		return $this;
	}


	public function hasHeader(string $name) : bool {
		if (empty($name)) throw new \ErrorException();

		return array_key_exists($name, $this->_headers);
	}

	public function numHeader(string $name) : int {
		if (empty($name)) throw new \ErrorException();

		return array_key_exists($name, $this->_headers) ? count($this->_headers[$name]) : 0;
	}


	public function getHeader(string $name, int $index = 0) : string {
		if (
			empty($name) || !array_key_exists($name, $this->_headers) ||
			$index < 0 || $index >= count($this->_headers[$name])
		) throw new \ErrorException();

		return $this->_headers[$name][$index];
	}

	public function setHeader(string $name, string $content, int $index = 0) : IHttpMessage {
		if (empty($name) || $index < 0) throw new \ErrorException();

		if (!array_key_exists($name, $this->_headers)) $this->_headers[$name] = [];

		$index = min($index, count($this->_headers[$name]));

		$this->_headers[$name][$index] = $content;

		return $this;
	}

	public function clearHeader(string $name) : IHttpMessage {
		if (empty($name)) throw new \ErrorException();

		if (array_key_exists($name, $this->_headers)) unset($this->_headers[$name]);

		return $this;
	}


	public function insertHeader(string $name, string $content, int $index) : IHttpMessage {
		if (empty($name) || $index < 0) throw new \ErrorException();

		if (!array_key_exists($name, $this->_headers)) $this->_headers[$name] = [];

		$index = min($index, count($this->_headers[$name]));

		array_splice($this->_headers[$name], $index, 0, $content);

		return $this;
	}

	public function appendHeader(string $name, string $content) : IHttpMessage {
		if (empty($name)) throw new \ErrorException();

		if (!array_key_exists($name, $this->_headers)) $this->_headers[$name] = [];

		$this->_headers[$name][] = $content;

		return $this;
	}

	public function removeHeader(string $name, int $index = 0) : IHttpMessage {
		if (empty($name) || $index < 0) throw new \ErrorException();

		if (array_key_exists($name, $this->_headers)) {
			$index = min($index, count($this->_headers[$name]) - 1);

			array_splice($this->_headers[$name], $index, 1);

			if (count($this->_headers[$name]) === 0) unset($this->_headers[$name]);
		}

		return $this;
	}


	public function iterateHeaders(array $order = []) : \Generator {
		$headers = $this->_headers;

		foreach ($order as $name) {
			if (!array_key_exists($name, $headers)) continue;

			foreach($headers[$name] as $item) yield $name => $item;

			unset($headers[$name]);
		}

		foreach ($headers as $name => $items) {
			foreach ($items as $item) yield $name => $item;
		}
	}


	public function getBody() : string {
		return $this->_body;
	}

	public function setBody(string $body) : IHttpMessage {
		$this->_body = $body;

		return $this;
	}


	public function __toString() : string {
		$eol = "\r\n";
		$message = $this->getStartLine() . $eol;

		foreach ($this->iterateHeaders() as $name => $content) $message .= $name . ': ' . $content . $eol;

		return $message . $eol . $this->getBody();
	}
}
