<?php

namespace lola\input;

use lola\input\Validator;
use lola\route\Route;


abstract class ARouteValidator
{
	
	const VERSION = '0.2.3';
	
	
	private $_validator = null;
	private $_route = null;
	
	
	public function isValid() {
		return $this->_useValidator()->isValid();
	}
	
	
	public function hasRoute() {
		return !is_null($this->_route);
	}
	
	public function& useRoute() {
		if (is_null($this->_route)) throw new \ErrorException();
		
		return $this->_route;
	}
	
	public function setRoute(Route& $route) {
		$this->_route =& $route;
		
		return $this;
	}
	
	
	protected function& _useValidator() {
		if (is_null($this->_validator)) $this->_validator = new Validator();
		
		return $this->_validator;
	}
}
