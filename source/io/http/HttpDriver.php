<?php

namespace lola\io\http;

use lola\inject\IInjector;
use lola\inject\IInjectable;

use lola\io\IRequestReplyDriver;
use lola\type\IStateTransform;
use lola\io\IRequest;
use lola\io\IReply;
use lola\io\IClient;
use lola\io\connect\IConnection;
use lola\io\mime\IMimePayload;

use lola\io\mime\MimePayload;



class HttpDriver
implements IHttpDriver, IInjectable
{

	static public function getDependencyConfig(array $config) {
		return [ 'injector:' ];
	}



	private $_injector;

	private $_request;
	private $_requestPayload;
	private $_client;
	private $_reply;
	private $_replyPayload;
	private $_cookies;

	private $_config;
	private $_connection;
	private $_requestMessage;
	private $_replyMessage;
	private $_transform;


	public function __construct(IInjector& $injector) {
		$this->_injector =& $injector;

		$this->_request = null;
		$this->_requestPayload = null;
		$this->_client = null;
		$this->_reply = null;
		$this->_cookies = null;

		$this->_config = null;
		$this->_connection = null;
		$this->_requestMessage = null;
		$this->_replyMessage = null;
		$this->_transform = null;
	}


	public function& useRequest() : IRequest {
		if (is_null($this->_request)) $this->_request = new HttpRequest($this);

		return $this->_request;
	}

	public function& useRequestPayload() : IMimePayload {
		if (is_null($this->_requestPayload)) $this->_requestPayload = new MimePayload($this->useRequest(), $this->useConfig());

		return $this->_requestPayload;
	}

	public function& useClient() : IClient {
		if (is_null($this->_client)) $this->_client = new HttpClient($this);

		return $this->_client;
	}

	public function& useReply() : IReply {
		if (is_null($this->_reply)) $this->_reply = new HttpReply($this);

		return $this->_reply;
	}

	public function& useReplyPayload() : IMimePayload {
		if (is_null($this->_replyPayload)) $this->_replyPayload = new MimePayload($this->useReply(), $this->useConfig());

		return $this->_replyPayload;
	}

	public function& useCookies() : IHttpCookies {
		if (is_null($this->_cookies)) $this->_cookies = new HttpCookies($this);

		return $this->_cookies;
	}


	public function& useConfig() : IHttpConfig {
		if (is_null($this->_config)) $this->_config = new HttpConfig();

		return $this->_config;
	}

	public function setConfig(IHttpConfig& $config) : IHttpDriver {
		$this->_config = $config;

		return $this;
	}


	public function& useConnection() : IConnection {
		if (is_null($this->_connection)) $this->_connection = $this->_injector
			->produce(\lola\io\connect\RemoteConnectionFactory::class)
			->getConnection();

		return $this->_connection;
	}

	public function setConnection(IConnection & $connection) : IRequestReplyDriver {
		$this->_connection = $connection;

		return $this;
	}


	public function& useRequestMessage() : IHttpMessage {
		if (is_null($this->_requestMessage)) $this->_requestMessage = $this->_injector
			->produce(RemoteRequestFactory::class)
			->getMessage();

		return $this->_requestMessage;
	}

	public function setRequestMessage(IHttpMessage& $message) : IHttpDriver {
		$this->_requestMessage =& $message;

		return $this;
	}


	public function& useReplyMessage() : IHttpMessage {
		if (is_null($this->_replyMessage)) $this->_replyMessage = $this->_injector
			->produce(RemoteReplyFactory::class)
			->getMessage();

		return $this->_replyMessage;
	}

	public function setReplyMessage(IHttpMessage& $message) : IHttpDriver {
		$this->_replyMessage =& $message;

		return $this;
	}


	public function& useReplyTransform() : IStateTransform {
		if (is_null($this->_transform)) $this->_transform = new HttpReplyTransform();

		return $this->_transform;
	}

	public function setReplyTransform(IStateTransform& $transform) : IHttpDriver {
		$this->_transform =& $transform;

		return $this;
	}


	public function sendReply() {
		$this
			->useReplyTransform()
			->setTarget($this)
			->process();
	}
}
