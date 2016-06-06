<?php

namespace lola\model;

use lola\type\APriorityQueue;



final class ModelActionQueue
extends APriorityQueue
{
	
	const PRIO_UPDATE = 1;
	const PRIO_INTERCEPT = 2;
	
	
	
	public function process(AActionModel& $model, ModelActionLog $log) {
		foreach ($this->_cbs as $cb) call_user_func_array($cb, [ $model, $log ]);
		
		return $this;
	}
}
