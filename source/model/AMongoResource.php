<?php

namespace lola\model;

use lola\model\IResource;

use MongoDB\Collection;
use MongoDB\BSON\ObjectID;
use lola\type\StructuredData;



abstract class AMongoResource
implements IResource
{
	
	const VERSION = '0.2.4';
	
	
	
	/**
	 * Returns the default serialization rules
	 * @return array
	 */
	static public function getDefaultDeserialization() {
		return [
			'root' => 'array',
			'document' => 'array',
			'array' => 'array'
		];
	}
	
	
	/**
	 * Returns true if $id is a valid ObjectId, false otherwise
	 * @param mixed $id
	 * @return boolean
	 */
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
	
	protected $_id = null;
	protected $_data = null;
	protected $_dirty = false;
	
	protected $_life = 0;
	protected $_ops = 0;
		
	
	/**
	 * Creates a new instance
	 * @param \MongoDB\Collection $collection The MongoDB collection
	 */
	public function __construct(Collection $collection) {
		$this->_collection = $collection;
		$this->_deserialize = static::getDefaultDeserialization();
		
		$this->_id = null;
		$this->_data = null;
		$this->_dirty = false;
		
		$this->_life = self::STATE_NEW;
		$this->_ops = self::OP_NONE;
	}
	
	
	/**
	 * Returns true if the instance data has been changed, false otherwise
	 * @return boolean
	 */
	public function isDirty() {
		return $this->_dirty;
	}
	
	/**
	 * Returns true if the instance is live, false otherwise
	 * @return boolean
	 */
	public function isLive() {
		return $this->_life === self::STATE_LIVE;
	}
	
	
	/**
	 * Returns true if the instance was initialized through a create call, false otherwise
	 * @return boolean
	 */
	public function wasCreated() {
		return $this->_ops & self::OP_CREATE;
	}
	
	/**
	 * Returns true if the instance was initialized through a read call, false otherwise
	 * @return boolean
	 */
	public function wasRead() {
		return $this->_ops & self::OP_READ;
	}
	
	/**
	 * Returns true if the update method of the instance has been called, false otherwise
	 * @return boolean
	 */
	public function wasUpdated() {
		return $this->_ops & self::OP_UPDATE;
	}
	
	/**
	 * Returns true if the delete method of the instance has been called, false otherwise
	 * @return boolean
	 */
	public function wasDeleted() {
		return $this->_ops & self::OP_DELETE;
	}
	
	
	/**
	 * Returns the data of the instance
	 * @return array
	 * @throws \ErrorException if the instance is not live
	 */
	public function getData() {
		if ($this->_life !== self::STATE_LIVE) throw new \ErrorException();
		
		return $this->_data;
	}
	
	/**
	 * Sets the data of the instance
	 * @param \lola\type\StructuredData $data
	 * @return \lola\model\AMongoResource
	 * @throws \ErrorException if the instance is not live
	 */
	public function setData(StructuredData $data) {
		if ($this->_life !== self::STATE_LIVE) throw new \ErrorException();

		$this->_data = $data;
		$this->_dirty = true;
		
		return $this;
	}
	
	
	/**
	 * Creates a new document in the collection and initializes the instance with it
	 * @param \lola\type\StructuredData $data
	 * @return \lola\model\AMongoResource
	 * @throws \ErrorException if the instance is not new
	 */
	public function create(StructuredData $data) {
		if ($this->_life !== self::STATE_NEW) throw new \ErrorException();
		
		$this->_life = self::STATE_DEAD;
		$this->_ops = self::OP_CREATE;
		
		$ret = $this->_collection->insertOne($data->toArray());
		
		$id = $ret->getInsertedId();
		$data->addItem('_id', $id);
		
		$this->_id = $id;
		$this->_data = $data;
		$this->_life = self::STATE_LIVE;
		$this->_dirty = false;
		
		return $this;
	}
	
	/**
	 * Retrieves a document from the collection and initializes the instance with it
	 * @param \lola\model\IResourceQuery $query
	 * @return \lola\model\AMongoResource
	 * @throws \ErrorException if the instance is not new
	 * @throws \ErrorException if $query is not an AMongoResourceQuery instance
	 */
	public function read(IResourceQuery $query) {
		if (
			$this->_life !== self::STATE_NEW ||
			!($query instanceof AMongoResourceQuery)
		) throw new \ErrorException();
		
		$this->_life = self::STATE_DEAD;
		
		$options = [
			'typeMap' => $this->_deserialize,
			'sort' => $query->getSorting(),
			'limit' => 1
		];
		
		if ($query->isMatchingQuery()) $ret = $this->_collection->find($query->getQuery(), $options);
		else $ret = $this->_collection->aggregate($query->getQuery(), $options);
		
		$items = $ret->toArray();
		
		if (!empty($items)) {
			$this->_id = $items[0]['_id'];
			$this->_data = new StructuredData($items[0]);
			$this->_life = self::STATE_LIVE;
		}
		
		return $this;
	}
	
	/**
	 * Updates the document inside the collection with the instance data
	 * @return \lola\model\AMongoResource
	 * @throws \ErrorException if the instance is not live
	 */
	public function update() {
		if ($this->_life !== self::STATE_LIVE) throw new \ErrorException();
		
		if (!$this->_dirty) return $this;
		
		$this->_ops |= self::OP_UPDATE;
		
		$this->_collection->replaceOne([
			'_id' => [ '$eq' => $this->_id ]
		], $this->_data->toArray());		
		
		$this->_dirty = false;
		
		return $this;
	}
	
	/**
	 * Removes the document associated with the instance from the collection
	 * @return \lola\model\AMongoResource
	 * @throws \ErrorException if the instance is not live
	 */
	public function delete() {
		if ($this->_life !== self::STATE_LIVE) throw new \ErrorException();
		
		$this->_ops |= self::OP_DELETE;
		
		$this->_collection->deleteOne([
			'_id' => [ '$eq' => $this->_id ]
		]);
		
		unset($this->_id);
		unset($this->_data);
		
		$this->_dirty = false;
		$this->_life = self::STATE_DEAD;
		
		return $this;
	}
}
