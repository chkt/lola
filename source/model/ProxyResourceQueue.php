<?php

namespace lola\model;

use lola\type\AQueue;

use lola\type\StructuredData;



final class ProxyResourceQueue
extends AQueue
{
	
	const VERSION = '0.2.4';
	
	
	
	public function process(StructuredData $data = null) {		
		foreach ($this->_items as $cb) call_user_func($cb, !is_null($data) ? $data->toArray() : null);		//LEGACY should send structured data to consumers
		
		return $this;
	}
}
