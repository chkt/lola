<?php

namespace chkt\ctrl;

use chkt\ctrl\AReplyController;



abstract class ATwigReplyController extends AReplyController {
	
	const VERSION = '0.0.6';
	
	
	protected $_twigView = '';
	
	
	public function __construct($view = 'default') {
		if (!is_string($view) || empty($view)) throw new \ErrorException();
		
		$this->_twigView = $view;
		
		$this->setReplyTransform(function($route, $reply) {
			return $this->_useInjected('app')->drawTwigView($this->_twigView, $route->getView(), $reply);
		});
	}
}
