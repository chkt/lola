<?php

namespace chkt\model;

use chkt\model\IResourceCollection;
use chkt\model\AMongoResource;

use MongoDB\Collection;
use chkt\model\IResourceQuery;
use chkt\model\AMongoResourceQuery;



abstract class AMongoResourceCollection
implements IResourceCollection
{
	
	const VERSION = '0.1.2';
	
	const STATE_NEW = 1;
	const STATE_LIVE = 2;
	const STATE_DEAD = 3;
	
	
	
	protected $_collection = null;
	protected $_deserialize = null;
	protected $_factory = null;
	
	protected $_data = null;
	protected $_resource = null;
	
	protected $_update = null;
	protected $_updateNum = 0;
	
	protected $_length = 0;
	protected $_life = 0;
	
	
	public function __construct(Collection $collection, Callable $resourceFactory) {		
		$this->_collection = $collection;
		$this->_deserialize = AMongoResource::getDefaultDeserialization();
		$this->_factory = $resourceFactory;
		
		$this->_data = null;
		$this->_resource = null;
		
		$this->_update = null;
		$this->_updateNum = 0;
		
		$this->_length = 0;
		$this->_life = self::STATE_NEW;
	}
	
	
	private function& _produceResource($index) {
		$ins = call_user_func($this->_factory);
		
		$ins->proxy(
				$this->_data[$index],
				function() use ($index) {
					$state =& $this->_update;

					if (!array_key_exists($index, $state)) {
						$this->_updateNum += 1;
						$state[$index] = 'update';
					}
				}, 
				function () use ($index) {
					$state =& $this->_update;
					
					if (!array_key_exists($index, $state)) $this->_updateNum += 1;
					
					$this->_update[$index] = 'delete';
				}
			);
			
		return $ins;
	}
	
	
	public function isLive() {
		return $this->_state === self::STATE_LIVE;
	}
	
	public function isDirty() {
		return $this->_updateNum !== 0;
	}
	
	
	public function read(IResourceQuery $query, $limit, $offset = 0) {
		if (
			!($query instanceof AMongoResourceQuery) ||
			!is_int($limit) || $limit < 0 ||
			!is_int($offset) || $offset < 0 ||
			$this->_life !== self::STATE_NEW
		) throw new \ErrorException();
		
		$this->_life = self::STATE_DEAD;
		
		$cursor = $this->_collection->find($query->getQuery(), [
			'typeMap' => $this->_deserialize,
			'sort' => [ '_id' => 1 ],
			'limit' => $limit,
			'skip' => $offset
		]);
		
		if (!is_null($cursor)) {
			$data = $cursor->toArray();
			
			$this->_data = $data;
			$this->_resource = [];
			
			$this->_update = [];
			
			$this->_length = count($data);
			$this->_life = self::STATE_LIVE;
		}
		
		return $this;
	}
	
	
	public function update() {
		if ($this->_life !== self::STATE_LIVE) throw new \ErrorException();
		
		if ($this->_updateNum === 0) return $this;
		
		$state = $this->_update;
				
		$update = array_filter($this->_data, function($key) use ($state) {
			return array_key_exists($key, $state) && $state[$key] === 'update';
		}, ARRAY_FILTER_USE_KEY);
		
		$delete = array_filter($this->_data, function($key) use ($state) {
			return array_key_exists($key, $state) && $state[$key] === 'delete';
		}, ARRAY_FILTER_USE_KEY);
		
		foreach ($delete as $document) $this->_collection->deleteOne([
			'_id' => [ '$eq' => $document['_id']]
		]);
		
		foreach ($update as $document) $this->_collection->replaceOne([
			'_id' => [ '$eq' => $document['_id']]
		], $document);
		
		return $this;
	}
	
	
	public function getLength() {
		if ($this->_life !== self::STATE_LIVE) throw new \ErrorException();
		
		return $this->_length;
	}
	
	
	public function& useItem($index) {
		if (
			!is_int($index) || $index < 0 ||
			$this->_life !== self::STATE_LIVE
		) throw new \ErrorException();
		
		if ($index > $this->_length - 1) return null;
		
		$resource =& $this->_resource;
		
		if (!array_key_exists($index, $resource)) $resource[$index] =& $this->_produceResource($index);
		
		return $resource[$index];
	}
}
