<?php

namespace lola\input;

use lola\input\ValidationException;



class Validator {
	
	const VERSION = '0.2.5';
	
	const STATE_NEW = 0;
	const STATE_VALID = 1;
	const STATE_INVALID = 2;
	
	const TYPE = 0;
	const TYPE_NULL = 1;
	const TYPE_BOOL = 7;
	const TYPE_INT = 2;
	const TYPE_UINT = 3;
	
	const TEST_TRUE = 4;
	const TEST_PROPERTY = 9;
	const USE_PROPERTY = 8;
	
	const SET_DEFAULT = 5;
	const SET_VALUE = 6;
	
	
	
	private $_state = 0;
	private $_exception = null;
	
	
	public function __construct() {
		$this->_state = self::STATE_NEW;
		$this->_exception = null;
	}
	
	
	private function _validateNull($value, $test = true) {		
		if (is_null($value) !== $test) throw new ValidationException($value);
		
		return $value;
	}
	
	private function _validateBool($value, $test = true) {
		if (is_bool($value) !== $test) throw new ValidationException($value);
		
		return $value;
	}
	
	private function _validateInt($value, $test = true) {
		if (is_int($value) !== $test) throw new ValidationException($value);
		
		return $value;
	}
	
	private function _validateUint($value, $test = true) {
		if ((is_int($value) && $value >= 0) !== $test) throw new ValidationException($value);
		
		return $value;
	}
	
	private function _validateArray($value) {
		if (!is_array($value)) throw new ValidationException($value);
		
		return $value;
	}
	
	private function _validateProperty($value, $prop) {
		$this->_validateArray($value);
		
		if (!array_key_exists($prop, $value)) throw new ValidationException($value);
		
		return $value;
	}
	
	
	private function _validateTest($value, callable $fn, $test = true) {
		if (call_user_func($fn, $value) !== $test) throw new ValidationException($value);
		
		return $value;
	}
	
	
	private function _castNull($value) {
		return null;
	}
	
	private function _castBool($value) {
		return $this->_validateBool((bool) $value);
	}
	
	private function _castInt($value) {
		return $this->_validateInt((int) $value);
	}
	
	private function _castUint($value) {		
		return $this->_validateUint((int) $value);
	}
	
	private function _getCastTransform($type) {
		$map = [
			self::TYPE_NULL => '_castNull',
			self::TYPE_BOOL => '_castBool',
			self::TYPE_INT => '_castInt',
			self::TYPE_UINT => '_castUint'
		];
		
		if (!array_key_exists($type, $map)) throw new \ErrorException();
		
		return $map[$type];
	}
	
	
	private function _useProperty($source, $prop) {
		$this->_validateProperty($source, $prop);
		
		return $source[$prop];
	}
	
	
	private function _setDefault($value, $default) {
		if (!is_null($this->_exception)) {
			$this->_exception = null;
			
			$value = $default;
		}
		
		return $value;
	}
	
	private function _setValue($value) {
		$this->_exception = null;
		
		return $value;
	}
	
	
	private function _validateRule($value, $rule, $condition) {
		switch ($rule) {
			case self::TYPE :
				$fn = $this->_getCastTransform($condition);
				
				return $this->$fn($value);
				
			case self::TYPE_NULL :
				return $this->_validateNull($value, $condition);
				
			case self::TYPE_BOOL :
				return $this->_validateBool($value, $condition);
				
			case self::TYPE_INT :
				return $this->_validateInt($value, $condition);
				
			case self::TYPE_UINT :
				return $this->_validateUInt($value, $condition);
				
			case self::SET_DEFAULT :
				return $this->_setDefault($value, $condition);
				
			case self::SET_VALUE :
				return $this->_setValue($condition);
				
			case self::TEST_TRUE :
				return $this->_validateTest($value, $condition);
			
			case self::TEST_PROPERTY :
				return $this->_validateProperty($value, $condition);
				
			case self::USE_PROPERTY :
				return $this->_useProperty($value, $condition);
				
			default : throw new \ErrorException();
		}
	}
	
	
	public function isValid() {
		return $this->_state !== self::STATE_INVALID;
	}
	
	
	public function validate($value, array $rules) {
		$except =& $this->_exception;
		$res = $value;
		
		foreach ($rules as $rule => $condition) {
			try {
				$res = $this->_validateRule($res, $rule, $condition);
			}
			catch (ValidationException $ex) {
				$except = is_null($except) ? $ex : $except;
			}
		}
		
		if (!is_null($except)) {
			$this->_state = self::STATE_INVALID;
			$res = $value;
		}
		else $this->_state = self::STATE_VALID;
		
		return $res;
	}
	
	
	public function assert() {
		if ($this->_state === self::STATE_INVALID) throw !is_null($this->_exception) ? $this->_exception : new \ErrorException();
		
		return $this;
	}
}
