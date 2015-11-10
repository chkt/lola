<?php

namespace chkt\ctrl;

use chkt\ctrl\AReplyController;

use chkt\route\Route;



abstract class ATwigReplyController extends AReplyController {
	
	const VERSION = '0.0.6';
	
	
	protected $_twigView = 'default';
	
	
	protected function _defaultReplyTransform(Route $route, $reply) {		
		return $this->_useInjected('app')->drawTwigView($this->_twigView, $route->getView(), $reply);
	}
}
