<?php

namespace chkt\ctrl;

use chkt\type\NamedQueue;



class ControllerProcessor
extends NamedQueue
{	
	
	public function process(AReplyController& $ctrl) {
		foreach ($this->_cbs as $cb) call_user_func($cb, $ctrl);
		
		return $this;
	}
}
