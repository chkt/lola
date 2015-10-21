<?php

namespace chkt\ctrl;

use chkt\ctrl\AController;
use chkt\route\Route;

use chkt\http\HttpReply;



abstract class AReplyController extends AController {
	
	/**
	 * The version string
	 */
	const VERSION = '0.0.4';
	
	
	/**
	 * The reply route
	 * @var Route
	 */
	protected $_route = null;
	
	/**
	 * The reply
	 * @var \HttpReply
	 */
	protected $_reply = null;
	
	/**
	 * The reply transform
	 * @var \Callable 
	 */
	protected $_transform = null;
	
	
	
	/**
	 * The default reply transform function
	 * @param Route $route The route
	 * @param any   $reply The reply
	 * @return any
	 */
	protected function _defaultTransform(Route $route, $reply) {
		return $reply;
	}
	
	
	/**
	 * Returns a reference to the route
	 * @return Route
	 */
	public function &useRoute() {
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
	 * Returns a reference to the reply
	 * @return \HttpReply
	 */
	public function &useReply() {
		if (is_null($this->_reply)) $this->_reply = new HttpReply(200, HttpReply::MIME_HTML);
		
		return $this->_reply;
	}
	
	/**
	 * Sets the reply
	 * @param  \HttpReply $reply The reply
	 * @return AReplyController
	 */
	public function setReply(HttpReply $reply) {
		$this->_reply = $reply;
		
		return $this;
	}
	
	
	/**
	 * Returns a reference to the transform function
	 * @return Callable
	 */
	public function &useTransform() {
		if (is_null($this->_transform)) $this->_transform = [$this, '_defaultTransform'];
		
		return $this->_transform;
	}
	
	/**
	 * Sets the transform function
	 * @param  Callable         $transform The transform function
	 * @return AReplyController
	 */
	public function setTransform(Callable $transform) {
		$this->_transform = $transform;
		
		return $this;
	}
	
	
	/**
	 * Replies with the instance-action referenced by <code>$action</code>
	 * @param string $action The action
	 * @param Route  $route  The route
	 */
	public function enter($action, Route $route) {
		$this->_route = $route;
		
		$ret  = parent::enter($action, $route);		
		$body = call_user_func($this->useTransform(), $this->useRoute(), $ret);
		
		$this
			->useReply()
			->setContent($body)
			->send();
	}
}