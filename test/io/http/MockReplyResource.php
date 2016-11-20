<?php

namespace test\io\http;

use lola\io\http\IHttpReplyResource;



final class MockReplyResource
implements IHttpReplyResource
{

	private $_queue;


	public function __construct() {
		$this->_queue = [];
	}


	public function sendHeader(string $header) : IHttpReplyResource {
		$this->_queue[] = [
			'type' => 'header',
			'content' => $header
		];

		return $this;
	}

	public function sendCookie(string $name, string $value, int $expires) : IHttpReplyResource {
		$this->_queue[] = [
			'type' => 'cookie',
			'name' => $name,
			'value' => $value,
			'expires' => $expires
		];

		return $this;
	}

	public function sendBody(string $body) : IHttpReplyResource {
		$this->_queue[] = [
			'type' => 'body',
			'content' => $body
		];

		return $this;
	}


	public function popQueue() : array {
		$queue = $this->_queue;

		$this->_queue = [];

		return $queue;
	}
}
