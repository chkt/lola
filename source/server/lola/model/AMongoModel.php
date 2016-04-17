<?php

namespace lola\model;

use \lola\type\TInjectable;



abstract class AMongoModel implements IModel {
	use TInjectable;
	
	
	
	abstract static public function Create(Array $data);
	
	abstract static public function Read($id);
	
	
	static private function _getDefaultDeserialization() {
		return [
			'root' => 'array',
			'document' => 'array',
			'array' => 'array'
		];
	}
	
	
	
	protected $_db = null;
	protected $_deserialize;
	
	protected $_data = null;
	protected $_dirty = false;
	protected $_life = false;



	protected function __construct($dbid = 'default') {
		if (!is_string($dbid) || empty($dbid)) throw new \ErrorException();
		
		$this->_db = $this->_useInjected('db')->get($dbid);
		$this->_deserialize = static::_getDefaultDeserialization();
		
		$this->_data = [];
		$this->_dirty = false;
		$this->_life = false;
	}
	
	
	public function isDirty() {
		return $this->_dirty;
	}
	
	public function isLife() {
		return $this->_life;
	}
	

	public function& useDeserializationMap() {
		return $this->_deserialize;
	}
	
	
	public function getData() {
		return $this->_data;
	}
	
	public function setData(Array $data) {
		if ($this->_life) {
			$this->_data = $data;
			$this->_dirty = true;
		}
		
		return $this;
	}
	
	
	abstract public function update();
	
	abstract public function delete();
}
