<?php

namespace lola\io\http;

use lola\io\http\IHttpDriver;

use lola\io\IRequest;
use lola\io\IReply;
use lola\io\IClient;
use lola\io\http\IHttpCookies;
use lola\io\http\IHttpRequestResource;
use lola\io\http\IHttpConfig;
use lola\io\http\payload\IHttpPayload;

use lola\type\IStateTransform;
use lola\io\http\HttpRequest;
use lola\io\http\HttpClient;
use lola\io\http\HttpReply;
use lola\io\http\HttpCookies;
use lola\io\http\payload\HttpPayload;



class HttpDriver
implements IHttpDriver
{

	private $_request;
	private $_payload;
	private $_client;
	private $_reply;
	private $_cookies;

	private $_config;
	private $_requestResource;
	private $_replyResource;
	private $_transform;


	public function __construct() {
		$this->_request = null;
		$this->_payload = null;
		$this->_client = null;
		$this->_reply = null;
		$this->_cookies = null;

		$this->_config = null;
		$this->_requestResource = null;
		$this->_replyResource = null;
		$this->_transform = null;
	}


	public function& useRequest() : IRequest {
		if (is_null($this->_request)) $this->_request = new HttpRequest($this);

		return $this->_request;
	}

	public function& usePayload() : IHttpPayload {
		if (is_null($this->_payload)) $this->_payload = new HttpPayload($this);

		return $this->_payload;
	}

	public function& useClient() : IClient {
		if (is_null($this->_client)) $this->_client = new HttpClient($this);

		return $this->_client;
	}

	public function& useReply() : IReply {
		if (is_null($this->_reply)) $this->_reply = new HttpReply($this);

		return $this->_reply;
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


	public function& useRequestResource() : IHttpRequestResource {
		if (is_null($this->_requestResource)) $this->_requestResource = new HttpRequestResource();

		return $this->_requestResource;
	}

	public function setRequestResource(IHttpRequestResource& $resource) : IHttpDriver {
		$this->_requestResource =& $resource;

		return $this;
	}


	public function& useReplyResource() : IHttpReplyResource {
		if (is_null($this->_replyResource)) $this->_replyResource = new HttpReplyResource();

		return $this->_replyResource;
	}

	public function setReplyResource(IHttpReplyResource& $resource) : IHttpDriver {
		$this->_replyResource =& $resource;

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
