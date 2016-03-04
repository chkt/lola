<?php

namespace chkt\input;

use chkt\input\IField;



final class Field
implements IField {
	
	const VERSION = '0.0.6';
	
	const KEY_TYPE = 'type';
	const KEY_NAME = 'name';
	const KEY_VALUE = 'value';
	const KEY_IMMUTABLE = 'immutable';
	const KEY_VALIDATE = 'validate';
	
	const TYPE_SUBMIT = 'submit';
	
	const FLAG_SUBMIT = 0x1;
	const FLAG_IMMUTABLE = 0x2;
	
	
	
	static public function Validating($name, $value, Callable $cb, $flags = 0x0) {
		$target = new self($name, $value, $flags);
		
		$target->_validating = true;
		$target->_validate = $cb;
		
		return $target;
	}
	
	
	
	protected $_name = '';
	protected $_flags = 0x0;
	
	protected $_valueFirst = '';
	protected $_valueNow = '';
	
	protected $_immutable = false;
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
		
		$this->_immutable = (bool) ($flags &= self::FLAG_IMMUTABLE);
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
		
		if (!$this->_immutable) {
			if ($this->_validating) $this->_invalid = (int) call_user_func($this->_validate, $value, $this->_valueFirst);

			$this->_valueNow = $value;
		}
		
		return $this;
	}
	
	
	public function getValues(Array $filter = null) {
		return [ $this->_name => $this->_valueNow ];
	}
	
	public function setValues(Array $values) {
		foreach ($values as $name => $state) {
			if ($name !== $this->_name) {
				$this->_invalid = 1;
				
				continue;
			}
			
			$this->setValue((string) $state);
		}
		
		return $this;
	}
	
	public function mapValues(Array $values) {
		if (!empty(array_diff($values, (array) $this->_name))) $this->_invalid = 1;
		
		$this->setValue(in_array($this->_name, $values) ? (string) true : '');
		
		return $this;
	}
	
	
	public function getData() {
		return [
			'type' => 'single',
			'name' => $this->_name,
			'value' => $this->_valueNow,
			'values' => [ $this->_name => $this->_valueNow ],
			'changed' => $this->_valueNow !== $this->_valueFirst,
			'mutable' => !$this->_immutable,
			'valid' => !$this->_invalid,
			'validity' => $this->_invalid
		];
	}
	
	
	public function isEmpty() {
		return empty($this->_valueNow);
	}
	
	public function isNonEmpty() {
		return !empty($this->_valueNow);
	}
	
	
	public function isInitial() {
		return $this->_valueFirst === $this->_valueNow;
	}
	
	public function isChanged() {
		return $this->_valueFirst !== $this->_valueNow;
	}

	
	public function isMutable() {
		return !$this->_immutable;
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
	
	public function isMultiple() {
		return false;
	}
	
	
	public function invalidate($state) {
		if (!is_int($state) || $state < 0) throw new \ErrorException();
		
		$this->_invalid = $state;
		
		return $this;
	}
}
