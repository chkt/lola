<?php

namespace lola\model;

use lola\model\IResource;

use MongoDB\Collection;
use MongoDB\BSON\ObjectID;



abstract class AMongoResource
implements IResource
{
	
	const VERSION = '0.1.2';
	
	
	
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
		if ($id instanceof ObjectID) return true;
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
	protected $_ops = 0;
		
	
	public function __construct(Collection $collection = null) {
		$this->_collection = $collection;
		$this->_deserialize = static::getDefaultDeserialization();
		$this->_sort = static::getDefaultSorting();
		
		$this->_data = null;
		$this->_dirty = false;
		
		$this->_life = self::STATE_NEW;
		$this->_ops = self::OP_NONE;
	}
	
	
	protected function _read(Array $query, $aggregate = false) {
		if (
			$this->_life !== self::STATE_NEW ||
			is_null($this->_collection)
		) throw new \ErrorException();
		
		$this->_life = self::STATE_DEAD;
		$this->_ops = self::OP_READ;
		
		if ($aggregate) {
			$items = $this->_collection->aggregate($query, [
				'typeMap' => $this->_deserialize,
				'limit' => 1,
				'sort' => $this->_sort
			])->toArray();
			
			if (empty($items)) $data = null;
			else $data = $items[0];
		}
		else $data = $this->_collection->findOne($query, [
			'typeMap' => $this->_deserialize,
			'sort' => $this->_sort
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
	
	
	public function wasCreated() {
		return $this->_ops & self::OP_CREATE;
	}
	
	public function wasRead() {
		return $this->_ops & self::OP_READ;
	}
	
	public function wasUpdated() {
		return $this->_ops & self::OP_UPDATE;
	}
	
	public function wasDeleted() {
		return $this->_ops & self::OP_DELETE;
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
		
		$this->_life = self::STATE_DEAD;
		$this->_ops = self::OP_CREATE;
		
		$ret = $this->_collection->insertOne($data);
		$data['_id'] = $ret->getInsertedId();
		
		$this->_data = $data;
		$this->_life = self::STATE_LIVE;
		$this->_dirty = false;
		
		return $this;
	}
	
	public function read(IResourceQuery $query) {
		if (!($query instanceof AMongoResourceQuery)) throw new \ErrorException();
		
		$this->_read($query->getQuery(), $query->isAggregationQuery());
		
		return $this;
	}
	
	public function update() {
		if ($this->_life !== self::STATE_LIVE) throw new \ErrorException();
		
		if (!$this->_dirty) return $this;
		
		$this->_ops |= self::OP_UPDATE;
		
		$this->_collection->replaceOne([
			'_id' => [ '$eq' => $this->_data['_id']]
		], $this->_data);		
		
		$this->_dirty = false;
		
		return $this;
	}
	
	public function delete() {
		if ($this->_life !== self::STATE_LIVE) throw new \ErrorException();
		
		$this->_ops |= self::OP_DELETE;
		
		$this->_collection->deleteOne([
			'_id' => [ '$eq' => $this->_data['_id']]
		]);
		
		unset($this->_data);
		
		$this->_dirty = false;
		$this->_life = self::STATE_DEAD;
		
		return $this;
	}
}
