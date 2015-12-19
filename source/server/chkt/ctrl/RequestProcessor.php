<?php

namespace chkt\ctrl;

use chkt\type\NamedQueue;

use chkt\route\Route;
use chkt\http\HttpRequest;



class RequestProcessor extends NamedQueue {
	
	public function process(HttpRequest& $request, Route& $route) {
		foreach($this->_cbs as $cb) call_user_func($cb, $request, $route);
		
		return $this;
	}
}
