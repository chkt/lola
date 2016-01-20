<?php

namespace chkt\input;

use chkt\inject\IInjectable;

use chkt\input\Processor;

use chkt\http\HttpRequest;



class Form
implements IInjectable
{
	
	const VERSION = '0.1.1';
	
	
	
	static public function getDependencyConfig(Array $config) {
		return [];
	}
	
	
	
	protected $_processor = null;
	
	
	public function __construct(Array $fields, Callable $cb = null) {
		$processor = Processor::Fields($fields);
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
