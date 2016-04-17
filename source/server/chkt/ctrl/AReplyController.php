<?php

namespace lola\ctrl;

use lola\ctrl\AController;
use lola\route\Route;

use lola\http\HttpRequest;
use lola\http\HttpReply;

use lola\ctrl\ControllerProcessor;



abstract class AReplyController extends AController {
	
	/**
	 * The version string
	 */
	const VERSION = '0.1.3';
	
	
	
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
	
			
	protected $_requestProcessor = null;
	
	protected $_replyProcessor = null;
	
	
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
	 * Returns a reference to the request processor
	 * @return RequestProcessor
	 */
	public function& useRequestProcessor() {
		if (is_null($this->_requestProcessor)) $this->_requestProcessor = new ControllerProcessor();
		
		return $this->_requestProcessor;
	}
	
	/**
	 * Sets the request processor
	 * @param RequestProcessor $processor
	 * @return AReplyController
	 */
	public function setRequestProcessor(ControllerProcessor $processor) {
		$this->_requestProcessor = $processor;
		
		return $this;
	}
	
	
	/**
	 * Returns a reference to the reply processor
	 * @return ReplyProcessor
	 */
	public function& useReplyProcessor() {
		if (is_null($this->_replyProcessor)) $this->_replyProcessor = new ControllerProcessor();
		
		return $this->_replyProcessor;
	}
	
	/**
	 * Sets the reply processor
	 * @param ReplyProcessor $processor
	 * @return AReplyController
	 */
	public function setReplyProcessor(ControllerProcessor $processor) {
		$this->_replyProcessor = $processor;
		
		return $this;
	}
	
	
	/**
	 * Replies with the instance-action referenced by $route
	 * @param Route& $route The route
	 */
	public function enter(Route& $route) {
		$this->_route =& $route;
		
		if (!is_null($this->_requestProcessor)) $this->useRequestProcessor()->process($this);
		
		$ret = parent::enter($route);
		
		if (isset($ret) && !is_null($ret)) $route->useActionResult()->pushItem($ret);
		
		$this->useReplyProcessor()->process($this);
		$this->useReply()->send();
	}
}
