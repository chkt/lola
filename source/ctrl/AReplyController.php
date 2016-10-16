<?php

namespace lola\ctrl;

use lola\ctrl\AController;
use lola\route\Route;

use lola\http\HttpRequest;
use lola\http\HttpReply;
use lola\ctrl\ControllerTransform;



abstract class AReplyController extends AController {
	
	/**
	 * The version string
	 */
	const VERSION = '0.4.0';
	
	
	
	/**
	 * The reply route
	 * @var Route
	 */
	protected $_route = null;
	
	/**
	 * The request
	 * @var HttpRequest
	 */
	protected $_request = null;
	
	/**
	 * The reply
	 * @var HttpReply
	 */
	protected $_reply = null;
	
	
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
	 * Returns a reference to the request
	 * @return HttpRequest
	 */
	public function& useRequest() {
		if (is_null($this->_request)) $this->_request = new HttpRequest();
		
		return $this->_request;
	}
	
	/**
	 * Sets the request
	 * @param HttpRequest $request
	 */
	public function setRequest(HttpRequest $request) {
		$this->_request = $request;
		
		return $this;
	}
	
	
	/**
	 * Returns a reference to the reply
	 * @return HttpReply
	 */
	public function& useReply() {
		if (is_null($this->_reply)) $this->_reply = new HttpReply(200, HttpReply::MIME_HTML);
		
		return $this->_reply;
	}
	
	/**
	 * Sets the reply
	 * @param HttpReply $reply The reply
	 * @return AReplyController
	 */
	public function setReply(HttpReply $reply) {
		$this->_reply = $reply;
		
		return $this;
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
	public function setReplyProcessor(ControllerTransform $transform) {
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
