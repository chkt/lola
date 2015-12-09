<?php

namespace chkt\ctrl;

use chkt\ctrl\AReplyController;

use chkt\route\Route;
use chkt\http\HttpReply;



abstract class ATwigReplyController extends AReplyController {
	
	const VERSION = '0.0.8';
	
	
	
	public function __construct($view = 'default') {
		if (!is_string($view) || empty($view)) throw new \ErrorException();
				
		$this
			->useReplyProcessor()
			->append('view', function(Route $route, HttpReply& $reply) use ($view) {
				$view = $this->_useInjected('app')->drawTwigView($view, $route->getView(), $route->useVars());

				$reply->setContent($view);
			});
	}
}
