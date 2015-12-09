<?php

namespace chkt\ctrl;

use chkt\type\NamedQueue;

use chkt\route\Route;
use chkt\http\HttpReply;



class ReplyProcessor extends NamedQueue {
	
	/**
	 * Processes all callbacks
	 * @param Route $route The route instance
	 * @param HttpReply $reply The reply instance
	 * @return ReplyProcessor
	 */
	public function process(Route& $route, HttpReply& $reply) {
		foreach($this->_cbs as $cb) call_user_func($cb, $route, $reply);
		
		return $this;
	}
}
