<?php

namespace test\io\http;

require_once 'MockRequestResource.php';
require_once 'MockReplyResource.php';

use lola\io\http\HttpDriver;
use lola\io\http\IHttpRequestResource;
use lola\io\http\IHttpReplyResource;

use test\io\http\MockRequestResource;
use test\io\http\MockReplyResource;



final class MockDriver
extends HttpDriver
{

	private $_requestResource;
	private $_replyResource;

	private $_replyCallback;


	public function __construct() {
		parent::__construct();

		$this->_requestResource = null;
		$this->_replyResource = null;
		$this->_replyCallback = null;
	}


	public function& useRequestResource() : IHttpRequestResource {
		if (is_null($this->_requestResource)) $this->_requestResource = new MockRequestResource();

		return $this->_requestResource;
	}


	public function& useReplyResource() : IHttpReplyResource {
		if (is_null($this->_replyResource)) $this->_replyResource = new MockReplyResource();

		return $this->_replyResource;
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
