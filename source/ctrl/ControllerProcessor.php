<?php

namespace lola\ctrl;

use lola\type\NamedQueue;



class ControllerProcessor
extends NamedQueue
{	
	
	const VERSION = '0.1.6';
	
	
	
	private $_qkeys = null;
	private $_qcbs = null;
	
	
	public function __construct(array $callbacks = null) {
		parent::__construct($callbacks);
		
		$this->_qkeys = [];
		$this->_qcbs = [];
	}
	
	
	public function getQueuedNames() {
		return $this->_qkeys;
	}
	
	
	public function process(AReplyController& $ctrl) {
		$this->_qkeys = array_keys($this->_cbs);
		$this->_qcbs = array_values($this->_cbs);
		
		$keys =& $this->_qkeys;
		$cbs =& $this->_qcbs;
		
		while (count($cbs) !== 0) {
			array_shift($keys);
			$cb = array_shift($cbs);
			
			call_user_func_array($cb, [ & $ctrl ]);
		}
		
		return $this;
	}
	
	
	public function skip($name) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();
		
		$keys =& $this->_qkeys;
		
		$index = array_search($name, $keys);
		
		if ($index === false) throw new \ErrorException();
		
		array_splice($keys, $index, 1);
		array_splice($this->_qcbs, $index, 1);
		
		return $this;
	}
	
	
	public function skipAll() {
		$this->_qkeys = [];
		$this->_qcbs = [];
		
		return $this;
	}
}
