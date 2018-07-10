<?php

namespace lola\ctrl;

use eve\common\access\ITraversableAccessor;
use lola\io\http\IHttpDriver;



abstract class AReplyController
extends AController
{

	static public function getDependencyConfig(ITraversableAccessor $config) : array {
		return [ 'environment:io' ];
	}



	protected $_state = null;

	/**
	 * The http driver
	 * @var IHttpDriver
	 */
	protected $_httpDriver = null;

	/**
	 * The request transform
	 * @var ControllerTransform|null
	 */
	protected $_requestTransform = null;

	/**
	 * The reply transform
	 * @var ControllerTransform|null
	 */
	protected $_replyTransform = null;


	/**
	 * Creates a new instance
	 * @param IHttpDriver $http
	 */
	public function __construct(IHttpDriver& $http) {
		$this->_state = null;
		$this->_httpDriver =& $http;
	}


	public function useRoute() {
		return $this->_state;
	}

	public function setRoute(IControllerState $state) {
		$this->_state = $state;

		return $this;
	}


	/**
	 * Returns a reference to the http driver
	 * @return IHttpDriver
	 */
	public function& useDriver() {
		return $this->_httpDriver;
	}

	/**
	 * Sets the http driver
	 * @param IHttpDriver $driver
	 * @return AReplyController
	 */
	public function setDriver(IHttpDriver& $driver) {
		$this->_httpDriver = $driver;

		return $this;
	}

	/**
	 * Returns a reference to the request
	 * @return HttpRequest
	 */
	public function& useRequest() {
		return $this
			->useDriver()
			->useRequest();
	}

	/**
	 * Returns a reference to the reply
	 * @return HttpReply
	 */
	public function& useReply() {
		return $this
			->useDriver()
			->useReply();
	}


	/**
	 * Returns a reference to the request transform
	 * @return ControllerTransform
	 */
	public function& useRequestTransform() {
		if (is_null($this->_requestTransform)) $this->_requestTransform = new ControllerTransform();

		return $this->_requestTransform;
	}

	/**
	 * Sets the request transform
	 * @param ControllerTransform $transform
	 * @return AReplyController
	 */
	public function setRequestTransform(ControllerTransform $transform) {
		$this->_requestTransform = $transform;

		return $this;
	}


	/**
	 * Returns a reference to the reply transform
	 * @return ControllerTransform
	 */
	public function& useReplyTransform() {
		if (is_null($this->_replyTransform)) $this->_replyTransform = new ControllerTransform();

		return $this->_replyTransform;
	}

	/**
	 * Sets the reply transform
	 * @param ControllerTransform $transform
	 * @return AReplyController
	 */
	public function setReplyTransform(ControllerTransform $transform) {
		$this->_replyTransform = $transform;

		return $this;
	}


	public function enter(string $action, IControllerState $state) : IController {
		$this->_state = $state;

		if (!is_null($this->_requestTransform)) $this
			->useRequestTransform()
			->setTarget($this)
			->process();

		parent::enter($action, $state);

		if (!is_null($this->_replyTransform)) $this
			->useReplyTransform()
			->setTarget($this)
			->process();

		$this
			->useReply()
			->send();

		return $this;
	}
}
