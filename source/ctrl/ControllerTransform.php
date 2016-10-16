<?php

namespace lola\ctrl;

use lola\type\AStateTransform;

use lola\ctrl\AController;



class ControllerTransform
extends AStateTransform
{
	
	const VERSION = '0.4.0';
	
	
	
	public function setTarget(& $target) {
		if (!($target instanceof AController)) throw new \ErrorException();
		
		return parent::setTarget($target);
	}
}
