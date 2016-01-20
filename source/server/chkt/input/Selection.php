<?php

namespace chkt\input;

use chkt\input\IField;



final class Selection
implements IField
{
	
	const VERSION = '0.1.1';
	
	const TYPE_SWITCHES = 'switches';
	
	
	
	private $_name = '';
	private $_key0 = '';
	
	private $_statesFirst = null;
	private $_statesNow = null;

	private $_total = 0;
	private $_set = 0;
	private $_changed = 0;
	private $_invalid = 0;
	
	
	public function __construct($name, Array $states) {
		$num = count($states);
		$set = 0;
		
		if (
			!is_string($name) || empty($name) ||
			$num === 0
		) throw new \ErrorException();
		
		foreach ($states as $state) {
			if (!is_bool($state)) throw new ErrorException();
			
			$set += $state ? 1 : 0;
		}
		
		$this->_name = $name;
		$this->_key0 = array_keys($states)[0];
		
		$this->_statesFirst = $states;
		$this->_statesNow = $states;
		
		$this->_total = $num;
		$this->_set = $set;
		$this->_changed = 0;
		$this->_invalid = 0;
	}
	
	
	public function isInitial() {
		return $this->_changed === 0;
	}
	
	public function isChanged() {
		return $this->_changed !== 0;
	}
	
	public function isEmpty() {
		return $this->_set === 0;
	}
	
	public function isNonEmpty() {
		return $this->_set !== 0;
	}
	
	public function isValid() {
		return $this->_invalid === 0;
	}
	
	public function isValidating() {
		return true;
	}
	
	public function isSubmit() {
		return false;
	}
	
	public function isMultiple() {
		return $this->_total !== 1;
	}
	
	
	public function getName() {
		return $this->_name;
	}
	
	public function getValue() {
		return $this->getValues([ $this->_key0 ])[$this->_key0];
	}
	
	public function setValue($value) {
		if (!is_string($value)) throw new \ErrorException();
		
		return $this->setValues([ $this->_key0 => (bool) $value ]);
	}
	
	public function getValues(Array $filter = null) {
		if (is_null($filter)) return $this->_statesNow;
		
		$key = array_combine($filter, array_fill(0, count($filter), 1));
		
		return array_intersect_key($this->_statesNow, $key);
	}
	
	public function setValues(Array $values) {
		$states =& $this->_statesNow;
		
		foreach ($values as $name => $state) {
			if (!array_key_exists($name, $states)) {
				$this->_invalid = 1;
				
				continue;
			}
			
			$soon = (bool) $state;
			$now = $states[$name];
			$was = $this->_statesFirst[$name];
			
			if ($soon === $now) continue;

			$states[$name] = $soon;
			$this->_set += $soon ? 1 : -1;
			$this->_changed += $soon !== $was ? 1 : -1;
		}
		
		return $this;
	}
	
	public function getData() {
		return [
			'type' => 'multi',
			'name' => $this->_name,
			'value' => $this->_statesNow[$this->_key0] ? $this->_key0 : '',
			'values' => array_filter($this->_statesNow, function($name, $set) {
				return [
					'name' => $name,
					'set' => $set
				];
			}, ARRAY_FILTER_USE_BOTH),
			'changed' => $this->_changed !== 0,
			'valid' => $this->_invalid === 0,
			'validity' => $this->_invalid
		];
	}
	
	
	public function invalidate($state) {
		if (!is_int($state) || $state < 0) throw new \ErrorException();
		
		$this->_invalid = $state;
		
		return $this;
	}
}
