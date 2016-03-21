<?php

namespace chkt\model;



final class ProxyResourceDriver
{
	
	const VERSION = '0.1.4';
	
	const ACTION_CREATE = 'create';
	const ACTION_UPDATE = 'update';
	const ACTION_DELETE = 'delete';
	
	
	
	static public function isValidAction($action) {
		return in_array($action, [
			self::ACTION_CREATE,
			self::ACTION_UPDATE,
			self::ACTION_DELETE
		]);
	}
	
	
	
	private $_models = null;
	private $_resources = null;
	
	private $_callbacks = null;
	
	
	public function __construct() {
		$this->_models = [];
		$this->_resources = [];
		
		$this->_callbacks = [];
	}
	
	
	public function getData(AModel $model) {
		$key = array_search($model, $this->_models);
		
		if ($key === false) throw new \ErrorException();
		
		return $this->_resources[$key]->getData();
	}
	
	
	public function associate(AModel& $model, ProxyResource& $resource) {
		if (array_search($model, $this->_models) !== false) throw new \ErrorException();
		
		$this->_models[] = $model;
		$this->_resources[] = $resource;
		$this->_callbacks[] = [];
		
		return $this;
	}
	
	public function disassociate(AModel& $model) {
		$key = array_search($model, $this->_models);
		
		if ($key === false) throw new \ErrorException();
		
		unset($this->_models[$key]);
		unset($this->_resources[$key]);
		unset($this->_callbacks[$key]);
		
		return $this;
	}
	
	
	public function addListener(AModel& $model, $action, Callable $fn) {
		if (!self::isValidAction($action)) throw new \ErrorException();
		
		$key = array_search($model, $this->_models);
		
		if ($key === false) throw new \ErrorException();
		
		$callbacks =& $this->_callbacks[$key];
		
		if (!array_key_exists($action, $callbacks)) $callbacks[$action] = [];
		
		$callbacks[$action][] = $fn;
		
		return $this;
	}
	
	public function removeListener(AModel& $model, $action, Callable $fn) {
		if (!self::isValidAction($action)) throw new \ErrorException();
		
		$key = array_search($model, $this->_models);
		
		if ($key === false) throw new \ErrorException();
		
		$callbacks =& $this->_callbacks[$key];
		
		if (!array_key_exists($action, $callbacks)) throw new \ErrorException();
		
		$index = array_search($fn, $callbacks[$key]);
		
		if ($index === false) throw new \ErrorException();
		
		unset($callbacks[$key][$index]);
		
		if (empty($callbacks[$key])) unset($callbacks[$key]);
		
		return $this;
	}
	
	
	public function dispatch(ProxyResource& $resource, $action) {
		if (!self::isValidAction($action)) throw new \ErrorException();
		
		foreach ($this->_resources as $key => $item) {
			if ($item !== $resource) continue;
			
			$callbacks =& $this->_callbacks[$key];
			
			if (!array_key_exists($action, $callbacks)) continue;
			
			foreach ($callbacks[$action] as $cb) call_user_func($cb, $item->getData(), $action);
		}
		
		return $this;
	}
}
