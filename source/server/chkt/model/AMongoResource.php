<?php

namespace chkt\model;

use chkt\model\IResource;

use MongoDB\Collection;
use MongoDB\BSON\ObjectID;



abstract class AMongoResource
implements IResource
{
	
	const VERSION = '0.1.2';
	
	const STATE_NEW = 1;
	const STATE_LIVE = 2;
	const STATE_DEAD = 3;
	
	
	
	static public function getDefaultDeserialization() {
		return [
			'root' => 'array',
			'document' => 'array',
			'array' => 'array'
		];
	}
	
	static public function getDefaultSorting() {
		return [ '_id' => 1 ];
	}
	
	
	static public function isValidId($id) {
		if ($id instanceof ObjectId) return true;
		else if (!is_string($id)) return false;
		
		try {
			new ObjectId($id);
			
			return true;
		} 
		catch (Exception $ex) {}
		
		return false;
	}
	
	
	
	protected $_collection = null;
	protected $_deserialize = null;
	protected $_sort = null;
	
	protected $_data = null;
	protected $_dirty = false;
	protected $_life = 0;
	
	protected $_updateCb = null;
	protected $_deleteCb = null;
	
	
	public function __construct(Collection $collection = null) {
		$this->_collection = $collection;
		$this->_deserialize = static::getDefaultDeserialization();
		$this->_sort = static::getDefaultSorting();
		
		$this->_data = null;
		$this->_dirty = false;
		$this->_life = self::STATE_NEW;
		
		$this->_update = null;
		$this->_delete = null;
	}
	
	
	protected function _read(Array $query) {
		if (
			$this->_life !== self::STATE_NEW ||
			is_null($this->_collection)
		) throw new \ErrorException();
		
		$this->_life = self::STATE_DEAD;
		$this->_updateCb = [$this, '_update'];
		$this->_deleteCb = [$this, '_delete'];
		
		$data = $this->_collection->findOne($query, [
			'typeMap' => $this->_deserialize,
			'sort' => $this->_sort
		]);
		
		if (!is_null($data)) {
			$this->_data = $data;
			$this->_life = self::STATE_LIVE;
		}
	}
	
	private function _update() {
		$this->_collection->replaceOne([
			'_id' => [ '$eq' => $this->_data['_id']]
		], $this->_data);
	}
	
	private function _delete() {
		$this->_collection->deleteOne([
			'_id' => [ '$eq' => $this->_data['_id']]
		]);
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
		if (
			$this->_life !== self::STATE_NEW ||
			is_null($this->_collection)
		) throw new \ErrorException();
		
		$ret = $this->_collection->insertOne($data);
		$data['_id'] = $ret->getInsertedId();
		
		$this->_data = $data;
		$this->_life = self::STATE_LIVE;
		$this->_dirty = false;
		
		$this->_updateCb = [$this, '_update'];
		$this->_deleteCb = [$this, 'delete'];
		
		return $this;
	}
	
	public function proxy(Array& $data, Callable $update, Callable $delete) {
		if ($this->_life !== self::STATE_NEW) throw new \ErrorException();
		
		$this->_data =& $data;
		$this->_life = self::STATE_LIVE;
		$this->_dirty = false;
		
		$this->_updateCb = $update;
		$this->_deleteCb = $delete;
		
		return $this;
	}
	
	public function read(IResourceQuery $query) {
		if (!($query instanceof AMongoResourceQuery)) throw new \ErrorException();
		
		$this->_read($query->getQuery());
		
		return $this;
	}
	
	public function update() {
		if ($this->_life !== self::STATE_LIVE) throw new \ErrorException();
		
		if (!$this->_dirty) return $this;

		call_user_func($this->_updateCb);
		
		$this->_dirty = false;
		
		return $this;
	}
	
	public function delete() {
		if ($this->_life !== self::STATE_LIVE) throw new \ErrorException();
		
		call_user_func($this->_deleteCb);
		
		unset($this->_data);
		
		$this->_dirty = false;
		$this->_life = self::STATE_DEAD;
		
		return $this;
	}
}
