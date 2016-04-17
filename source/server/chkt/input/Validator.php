<?php

namespace lola\input;

use lola\input\ValidationException;



class Validator {
	
	const VERSION = '0.1.4';
	
	const STATE_NEW = 0;
	const STATE_VALID = 1;
	const STATE_INVALID = 2;
	
	const T_EMPTY = 0;
	const T_INVALID = 4;
	const V_VALUES = 1;
	const V_TRUE = 2;
	const V_TYPE = 3;
	
	const TYPE_STRING_NONEMPTY = 1;
	
	
	
	static private function _validateValues($val, Array $list) {
		return in_array($val, $list) ? $val : new ValidationException();
	}
	
	static private function _validateFunction($val, Callable $fn, $compare = true) {
		return call_user_func($fn, $val) === $compare ? $val : new ValidationException();
	}
	
	static private function _validateType($val, $type) {
		switch ($type) {
			case self::TYPE_STRING_NONEMPTY :
				return is_string($val) && !empty($val) ? $val : new ValidationException();
				
			default : throw new \ErrorException();
		}
	}
	
	
	static private function _validateRule($value, $rule, $op) {
		$map = [
			self::V_TRUE => '_validateFunction',
			self::V_TYPE => '_validateType',
			self::V_VALUES => '_validateValues'
		];
		
		if (!array_key_exists($rule, $map)) throw new \ErrorException();
		
		$method = $map[$rule];
		$res = self::$method($value, $op);
		
		if ($res instanceof ValidationException) throw $res;
		
		return $res;
	}
	
	
	
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
		foreach ($rules as $rule => $op) {
			try {
				$value = self::_validateRule($value, $rule, $op);
			}
			catch (ValidationException $ex) {
				if (array_key_exists(self::V_DEFAULT_INVALID, $rules)) $value = $rules[self::V_DEFAULT_INVALID];
				else {
					$this->_state = self::STATE_INVALID;
					$this->_exception = $ex;
				}
				
				break;
			}
		}
		
		return $value;
	}
	
	
	public function validateProperty(Array $source, $prop, Array $rules) {
		
	}
	
	public function validateProperties(Array $source, Array $props) {
	}
	
	
	public function assert() {
		if ($this->_state === self::STATE_INVALID) throw !is_null($this->_exception) ? $this->_exception : new \ErrorException();
		
		return $this;
	}
}
