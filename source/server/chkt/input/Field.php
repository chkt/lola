<?php

namespace chkt\input;



class Field {
	
	const VERSION = '0.0.6';
	
	const FLAG_SUBMIT = 0x1;
	
	
	
	static public function Validating($name, $value, Callable $cb, $flags = 0x0) {
		$target = new self($name, $value, $flags);
		
		$target->_validating = true;
		$target->_validate = $cb;
		
		return $target;
	}
	
	
	static public function fromArray(Array $data) {
		return array_map(function($item) {
			$flags = 0x0;
			
			if (array_key_exists('type', $item) && $item['type'] === 'submit') $flags |= self::FLAG_SUBMIT;
			
			if (array_key_exists('validate', $item)) return self::Validating($item['name'], $item['value'], $item['validate'], $flags);
			
			if (array_key_exists('fixed', $item) && $item['fixed']) return self::Fixed ($item['name'], $item['value'], $flags);
			
			return new self($item['name'], $item['value'], $flags);
		}, $data);
	}
	
	
	
	protected $_name = '';
	protected $_flags = 0x0;
	
	protected $_valueFirst = '';
	protected $_valueNow = '';
		
	protected $_validating = false;
	protected $_invalid = 0;
	protected $_validate = null;
	
	
	public function __construct($name, $value, $flags = 0x0) {
		if (
			!is_string($name) || empty($name) || 
			!is_string($value) ||
			!is_int($flags)
		) throw new \ErrorException();
		
		$this->_name = $name;
		$this->_flags = $flags;
		
		$this->_valueFirst = $value;
		$this->_valueNow = $value;
				
		$this->_validating = false;
		$this->_invalid = 0;
		$this->_validate = null;
	}
	
	
	public function getName() {
		return $this->_name;
	}
	
	
	public function getValue() {
		return $this->_valueNow;
	}
	
	public function setValue($value) {
		if (!is_string($value)) throw new \ErrorException();
				
		if ($this->_validating) $this->_invalid = (int) call_user_func($this->_validate, $value, $this->_valueFirst);
		
		$this->_valueNow = $value;
		
		return $this;
	}
	
	
	public function getData() {
		return [
			'name' => $this->_name,
			'value' => $this->_valueNow,
			'changed' => $this->_valueNow !== $this->_valueFirst,
			'valid' => !$this->_invalid,
			'validity' => $this->_invalid
		];
	}
	
	
	public function isEmpty() {
		return empty($this->_valueNow);
	}
	
	public function isInitial() {
		return $this->_valueFirst === $this->_valueNow;
	}

	public function isValidating() {
		return $this->_validating;
	}
	
	public function isValid() {
		return !$this->_invalid;
	}
	
	
	public function isSubmit() {
		return $this->_flags & self::FLAG_SUBMIT;
	}
	
	
	public function invalidate($state) {
		if (!is_int($state) || $state < 0) throw new \ErrorException();
		
		$this->_invalid = $state;
		
		return $this;
	}
}