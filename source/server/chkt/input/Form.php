<?php

namespace chkt\input;

use \chkt\input\Field;
use \chkt\input\Processor;

use \chkt\http\HttpRequest;



class Form {
	
	const VERSION = '0.0.6';
	
	
	
	protected $_processor = null;
	
	
	public function __construct(Array $fields, Callable $cb = null) {
		$processor = new Processor(Field::fromArray($fields));
		$processor->setValidationCallback($cb);
		
		$this->_processor = $processor;
	}
	
	
	public function validate(HttpRequest $request) {
		if ($request->getMethod() === HttpRequest::METHOD_GET) return false;
		
		$data = $request->getPayload();
		
		$this->_processor->validate($data);
		
		return $this->_processor->getState() === Processor::STATE_VALID;
	}
	
	public function invalidate() {
		$processor =& $this->_processor;
				
		$processor->setState($processor->getState() & ~Processor::FLAG_VALID);
		
		return $this;
	}
	
	
	public function getData() {
		return $this->_processor->getData();
	}
}