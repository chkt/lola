<?php

namespace test\io\http;

use lola\io\connect\IConnection;
use lola\io\connect\Connection;
use lola\io\http\HttpDriver;
use lola\io\http\IHttpMessage;
use lola\io\http\IHttpReplyResource;
use lola\io\http\HttpMessage;



final class MockDriver
extends HttpDriver
{

	private $_connection;
	private $_requestResource;
	private $_replyResource;

	private $_replyCallback;


	public function __construct() {
		$injector = new MockInjector();

		parent::__construct($injector);

		$this->_connection = null;
		$this->_requestResource = null;
		$this->_replyResource = null;
		$this->_replyCallback = null;
	}

	public function& useConnection() : IConnection {
		if (is_null($this->_connection)) $this->_connection = new Connection();

		return $this->_connection;
	}


	public function& useRequestMessage() : IHttpMessage {
		if (is_null($this->_requestResource)) $this->_requestResource = new HttpMessage('');

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
