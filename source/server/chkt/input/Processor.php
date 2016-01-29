<?php

namespace chkt\input;

use chkt\input\IField;
use chkt\input\Field;



class Processor {
	
	const VERSION = '0.0.6';
	
	const FLAG_VALIDATE = 0x1;
	const FLAG_COMMIT = 0x2;
	const FLAG_VALID = 0x4;
	const FLAG_MODIFIED = 0x8;
	
	const STATE_UNVALIDATED = 0x0;
	const STATE_UNCOMMITED = 0x1;
	const STATE_UNMODIFIED = 0x3;
	const STATE_INVALID = 0xa;
	const STATE_VALID = 0xf;
	
	
	
	static public function Fields(Array $data) {
		return new Processor(array_map(function($item) {
			if (!array_key_exists(Field::KEY_NAME, $item)) throw new \ErrorException();
			
			$name = $item[Field::KEY_NAME];
			$type = array_key_exists(Field::KEY_TYPE, $item) ? $item[Field::KEY_TYPE] : '';
			$validate = array_key_exists(Field::KEY_VALIDATE, $item) ? $item[Field::KEY_VALIDATE] : null;
			$flags = 0x0;
			
			if ($type === Selection::TYPE_SWITCHES) return new Selection($name, $item['values']);
			
			if ($type === Field::TYPE_SUBMIT) $flags |= Field::FLAG_SUBMIT;
			
			if (!is_null($validate)) return Field::Validating ($name, $item['value'], $validate, $flags);						
			else return new Field($name, $item['value'], $flags);
		}, $data));
	}
	
	
	
	private $_state = 0x0;
	
	private $_field = null;
	private $_submit = null;
	
	private $_validate = null;
	
	
	public function __construct(Array $fields = null) {
		$this->_state = self::STATE_UNVALIDATED;
		
		$this->_field = [];
		$this->_submit = [];
		
		$this->_validate = null;
		
		if (!is_null($fields)) $this->addFields($fields);
	}
	
	
	private function _isDataSubmitted(Array $data) {
		return !empty(array_filter($this->_submit, function($key) use ($data) {
			return array_key_exists($key, $data) && !empty($data[$key]);
		}));
	}
	
	
	public function getState() {
		return $this->_state;
	}
	
	public function setState($state) {
		if (!is_int($state) || $state < 0 || $state > self::STATE_VALID) throw new \ErrorException();
		
		$this->_state = $state;
	}
	
	
	public function& useField($name) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();
		
		$field =& $this->_field;
		
		if (!array_key_exists($name, $field)) throw new \ErrorException();
		
		return $field[$name];
	}
	
	public function getValue($name) {
		return $this->useField($name)->getValue();
	}
	
	
	public function getValidationCallback() {
		return $this->_validate;
	}
	
	public function setValidationCallback(Callable $cb = null) {
		$this->_validate = $cb;
	}
	
	
	public function addFields(Array $fields) {
		$ownField =& $this->_field;
		$ownSubmit =& $this->_submit;
		
		foreach($fields as $field) {
			if (!($field instanceof IField)) throw new \ErrorException();
			
			$name = $field->getName();
			$ownField[$name] = $field;
			
			if ($field->isSubmit() && array_search($name, $ownSubmit) === false) $ownSubmit[] = $name;
		}
		
		return $this;
	}
	
	public function removeFields(Array $fields) {
		$ownField =& $this->_field;
		$ownSubmit =& $this->_submit;
		
		foreach($fields as $field) {
			if (!($field instanceof Field)) throw new \ErrorException();
			
			$key = array_search($field, $ownField);
			
			if ($key === false) continue;
			
			unset($ownField[$key]);
			
			$index = array_search($key, $ownSubmit);
			
			if ($index !== false) array_splice($ownSubmit, $index, 1);
		}
		
		return $this;
	}
	
	
	public function validate(Array $data) {
		if ($this->_state !== self::STATE_UNVALIDATED) throw new \ErrorException();
		
		$this->_state |= self::FLAG_VALIDATE;
		
		if (!$this->_isDataSubmitted($data)) return $this;
		
		$this->_state |= self::FLAG_COMMIT;
		
		$initial = true;
		$valid = true;
		
		foreach($this->_field as $name => $field) {			
			if ($field->isMultiple()) $field->mapValues(array_key_exists($name, $data) ? $data[$name] : []);
			else $field->setValue(array_key_exists($name, $data) ? $data[$name] : '');
			
			$initial = $initial && $field->isInitial();
			$valid = $valid && $field->isValid();
		}
		
		if (!is_null($this->_validate)) call_user_func_array($this->_validate, [&$initial, &$valid]);
		
		if ($initial) return $this;
		
		$this->_state |= self::FLAG_MODIFIED;
		
		if ($valid) $this->_state |= self::FLAG_VALID;
		
		return $this;
	}
	
	
	public function getData() {		
		return [
			'states' => [
				'unvalidated' => self::STATE_UNVALIDATED,
				'unmodified' => self::STATE_UNMODIFIED,
				'invalid' => self::STATE_INVALID,
				'valid' => self::STATE_VALID
			],
			'state' => $this->_state,
			'field' => array_map(function($item) {
				return $item->getData();
			}, $this->_field)
		];
	}
}
