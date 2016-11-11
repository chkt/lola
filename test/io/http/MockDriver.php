<?php

namespace test\io\http;

require_once 'MockRequestResource.php';

use lola\io\http\HttpDriver;
use lola\io\http\IHttpRequestResource;

use test\io\http\MockRequestResource;



final class MockDriver
extends HttpDriver
{

	private $_resource;

	private $_replyCallback;


	public function __construct() {
		parent::__construct();

		$this->_resource = null;
		$this->_replyCallback = null;
	}


	public function& useRequestResource() : IHttpRequestResource {
		if (is_null($this->_resource)) $this->_resource = new MockRequestResource();

		return $this->_resource;
	}


	public function sendReply() {
		$reply =& $this->useReply();

		if (!is_null($this->_replyCallback)) call_user_func_array($this->_replyCallback, [& $reply ]);
	}

	public function setReplyCallback(callable $fn) : MockDriver {
		$this->_replyCallback = $fn;

		return $this;
	}
}
