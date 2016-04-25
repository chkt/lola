<?php

namespace lola\model;

use lola\type\AQueue;



final class ModelInterceptorQueue
extends AQueue
{
	public function process(array $data = null) {
		foreach ($this->_items as $cb) call_user_func($cb, ($clone = $data));
		
		return $this;
	}
}
