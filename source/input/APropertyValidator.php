<?php

namespace lola\input;

use lola\input\Validator;



abstract class APropertyValidator
{
	
	const VERSION = '0.2.5';
	
	
	private $_validator = null;
	private $_source = null;
	
	
	public function isValid() {
		return $this->_useValidator()->isValid();
	}
	
	
	public function hasSource() {
		return !is_null($this->_source);
	}
	
	public function& useSource() {
		if (is_null($this->_source)) throw new \ErrorException();
		
		return $this->_source;
	}
	
	public function setSource(array& $source) {
		$this->_source =& $source;
		
		return $this;
	}
	
	
	protected function& _useValidator() {
		if (is_null($this->_validator)) $this->_validator = new Validator();
		
		return $this->_validator;
	}
}
