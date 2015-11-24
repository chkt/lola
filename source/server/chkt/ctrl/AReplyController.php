<?php

namespace chkt\ctrl;

use chkt\ctrl\AController;
use chkt\route\Route;

use chkt\http\HttpRequest;
use chkt\http\HttpReply;



abstract class AReplyController extends AController {
	
	/**
	 * The version string
	 */
	const VERSION = '0.0.7';
	
	
	
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
	 * The reply transform
	 * @var \Callable 
	 */
	protected $_replyTransform = null;
	
	protected $_requestTransform = null;
	
	
	/**
	 * The default reply transform function
	 * @param Route $route The route
	 * @param any   $reply The reply
	 * @return any
	 */
	protected function _defaultReplyTransform(Route $route, $reply) {
		return (string) $reply;
	}
	
	protected function _defaultRequestTransform(Route $route, $action) {
		return (string) $action;
	}
	
	
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
	
	
	public function& useRequestTransform() {
		if (is_null($this->_requestTransform)) $this->_requestTransform = [$this, '_defaultRequestTransform'];
		
		return $this->_requestTransform;
	}
	
	public function setRequestTransform(Callable $transform) {
		$this->_requestTransform = $transform;
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
	 * Returns a reference to the reply transform function
	 * @return Callable
	 */
	public function& useReplyTransform() {
		if (is_null($this->_replyTransform)) $this->_replyTransform = [$this, '_defaultReplyTransform'];
		
		return $this->_replyTransform;
	}
	
	/**
	 * Sets the reply transform function
	 * @param  Callable $transform The transform function
	 * @return AReplyController
	 */
	public function setReplyTransform(Callable $transform) {
		$this->_replyTransform = $transform;
		
		return $this;
	}
	
	
	/**
	 * Replies with the instance-action referenced by <code>$action</code>
	 * @param string $action The action
	 * @param Route  $route  The route
	 */
	public function enter($action, Route $route) {
		$this->_route = $route;
		
		$target = !is_null($this->_requestTransform) ? call_user_func($this->useRequestTransform(), $this->useRoute(), $action) : $action;
		
		$ret  = parent::enter($target, $route);
		
		$body = !is_null($this->_replyTransform) ? call_user_func($this->useReplyTransform(), $this->useRoute(), $ret) : $ret;
		
		$this
			->useReply()
			->setContent($body)
			->send();
	}
}
