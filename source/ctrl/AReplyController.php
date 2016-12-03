<?php

namespace lola\ctrl;

use lola\ctrl\AController;
use lola\route\Route;

use lola\io\http\IHttpDriver;
use lola\io\http\HttpDriver;
use lola\ctrl\ControllerTransform;



abstract class AReplyController
extends AController
{

	/**
	 * The version string
	 */
	const VERSION = '0.5.1';



	/**
	 * The reply route
	 * @var Route
	 */
	protected $_route = null;

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
	 * Returns a reference to the route
	 * @return Route
	 */
	public function& useRoute() {
		return $this->_route;
	}

	/**
	 * Sets the route
	 * @param Route $route The route
	 * @return AReplyController
	 */
	public function setRoute(Route $route) {
		$this->_route = $route;

		return $this;
	}


	/**
	 * Returns a reference to the http driver
	 * @return IHttpDriver
	 */
	public function& useDriver() {
		if (is_null($this->_httpDriver)) $this->_httpDriver = new HttpDriver();

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


	/**
	 * Replies with the instance-action referenced by $route
	 * @param Route $route The route
	 */
	public function enter(Route& $route) {
		$this->_route =& $route;

		if (!is_null($this->_requestTransform)) $this
			->useRequestTransform()
			->setTarget($this)
			->process();

		$ret = parent::enter($route);

		if (isset($ret) && !is_null($ret)) $route
			->useActionResult()
			->pushItem($ret);

		if (!is_null($this->_replyTransform)) $this
			->useReplyTransform()
			->setTarget($this)
			->process();

		$this
			->useReply()
			->send();
	}
}
