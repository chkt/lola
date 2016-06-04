<?php

namespace lola\type;



abstract class APriorityQueue
{
	
	const VERSION = '0.2.1';
	
	const PRIO_HIGHEST = 0;
	const PRIO_LOWEST = PHP_INT_MAX;
	const PRIO_DEFAULT = 1;
	
	

	protected $_prios = null;	
	protected $_cbs = null;
	
	
	public function __construct() {
		$this->_prios = [];
		$this->_cbs = [];
	}
	
	
	public function has(callable $cb) {
		return array_search($cb, $this->_cbs) !== false;
	}
	
	
	public function getPrioOf(callable $cb) {
		$index = array_search($cb, $this->_cbs, true);
		
		if ($index === false) throw new \ErrorException();
		
		return $this->_prios[$index];
	}
	
	public function setPrioOf(callable $cb, $prio) {		
		return $this
			->remove($cb)
			->add($cb, $prio);
	}
	
	
	public function add(callable $cb, $prio = self::PRIO_DEFAULT) {
		if (
			array_search($cb, $this->_cbs, true) ||
			!is_int($prio) || $prio < 0
		) throw new \ErrorException();
		
		$prios =& $this->_prios;
		
		for ($index = count($prios) - 1; $index >= 0; $index -= 1) {
			if ($prios[$index] <= $prio) break;
		}
		
		array_splice($this->_cbs, $index + 1, 0, [ $cb ]);
		array_splice($this->_prios, $index + 1, 0, [ $prio]);
		
		return $this;
	}
	
	public function remove(callable $cb) {
		$index = array_search($cb, $this->_cbs, true);
		
		if ($index === false) throw new \ErrorException();
		
		array_splice($this->_cbs, $index, 1);
		array_splice($this->_prios, $index, 1);
		
		return $this;
	}
}
