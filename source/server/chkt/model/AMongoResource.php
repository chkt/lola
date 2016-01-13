<?php

namespace chkt\model;

use chkt\model\IResource;

use MongoDB\Collection;
use MongoDB\BSON\ObjectID;



abstract class AMongoResource implements IResource {
	
	const VERSION = '0.1.0';
	
	const STATE_NEW = 1;
	const STATE_LIVE = 2;
	const STATE_DEAD = 3;
	
	
	static private function _getDefaultDeserialization() {
		return [
			'root' => 'array',
			'document' => 'array',
			'array' => 'array'
		];
	}
	
	
	static public function isValidId($id) {
		return $id instanceof ObjectID;
	}
	
	
	
	protected $_collection = null;
	protected $_deserialize = null;
	
	protected $_data = null;
	protected $_dirty = false;
	protected $_life = 0;
	
	
	public function __construct(Collection $collection) {
		$this->_collection = $collection;
		$this->_deserialize = static::_getDefaultDeserialization();
		
		$this->_data = null;
		$this->_dirty = false;
		$this->_life = self::STATE_NEW;
	}
	
	
	protected function _read(Array $query) {
		if ($this->_life !== self::STATE_NEW) throw new \ErrorException();
		
		$this->_life = self::STATE_DEAD;
		
		$data = $this->_collection->findOne($query, [
			'typeMap' => $this->_deserialize
		]);
		
		if (!is_null($data)) {
			$this->_data = $data;
			$this->_life = self::STATE_LIVE;
		}
	}
	
	
	public function isDirty() {
		return $this->_dirty;
	}
	
	public function isLive() {
		return $this->_life === self::STATE_LIVE;
	}
	
	
	public function getData() {
		return $this->_data;
	}
	
	public function setData(Array $data) {
		if ($this->_life !== self::STATE_LIVE) throw new \ErrorException();

		$this->_data = $data;
		$this->_dirty = true;
		
		return $this;
	}
	
	
	public function create(Array $data) {
		if ($this->_life !== self::STATE_NEW) throw new \ErrorException();
		
		$ret = $this->_collection->insertOne($data);
		$data['_id'] = $ret->getInsertedId();
		
		$this->_data = $data;
		$this->_life = self::STATE_LIVE;
		$this->_dirty = false;
		
		return $this;
	}
	
	public function read(Array $map) {		
		$query = [];
		
		foreach ($map as $key => $value) $query[$key] = [ '$eq' => $value ];
		
		$this->_read($query);
		
		return $this;
	}
	
	public function update() {
		if ($this->_life !== self::STATE_LIVE) throw new \ErrorException();
		
		if (!$this->_dirty) return $this;
		
		$this->_collection->replaceOne([
			'_id' => [ '$eq' => $this->_data['_id']]
		], $this->_data);
		
		$this->_dirty = false;
		
		return $this;
	}
	
	public function delete() {
		if (!$this->_life !== self::STATE_LIVE) throw new \ErrorException();
		
		$this->_collection->deleteOne([
			'_id' => [ '$eq' => $this->_data['_id']]
		]);
		
		$this->_data = null;
		$this->_dirty = false;
		$this->_life = self::STATE_DEAD;
		
		return $this;
	}
}
