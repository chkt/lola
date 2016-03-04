<?php

namespace chkt\input;



class Validator {
	
	const VERSION = '0.1.4';
	
	const STATE_NEW = 0;
	const STATE_VALID = 1;
	const STATE_INVALID = 2;
	
	const V_DEFAULT = 0;
	const V_VALUES = 1;
	const V_TRUE = 2;
	const V_TYPE = 3;
	
	const TYPE_STRING_NONEMPTY = 1;
	
	
	
	
	private $_state = 0;
	private $_exception = null;
	
	
	public function __construct() {
		$this->_state = self::STATE_NEW;
		$this->_exception = null;
	}
	
	
	public function isValid() {
		return $this->_state !== self::STATE_INVALID;
	}
	
	
	public function validate($value, Array $rules) {
		
		$map = [
			self::V_VALUES => function ($val, Array $map) {
				if (in_array($val, $map)) return $val;
				
				throw new ValidatorException();
			},
			self::V_TRUE => function($val, Callable $fn) {
				if (call_user_func($fn, $val) === true) return $val;
				
				throw new ValidatorException();
			},
			self::V_TYPE => function($val, $type) {
				switch ($type) {
					case self::TYPE_STRING_NONEMPTY :
						if (is_string($val) && !empty($val)) return $val;
						
						break;
				}
				
				throw new ValidatorException();
			}
		];
		
		foreach ($rules as $rule => $op) {
			try {
				$value = $map[$rule]($op, $value);
			} catch (\Exception $ex) {
				$this->_state = self::STATE_INVALID;
				$this->_exception = $ex;
				
				$value = null;
			}
		}
		
		return $value;
	}
	
	
	public function validateProperty(Array $source, $prop, Array $rules) {
		
	}
	
	public function validateProperties(Array $source, Array $props) {
		foreach ($props as $items) {
			
		}
	}
	
	
	public function assert() {
		if ($this->_state === self::STATE_INVALID) throw !is_null($this->_exception) ? $this->_exception : new \ErrorException();
		
		return $this;
	}
}
