<?php

namespace lola\model;

use MongoDB\Collection;
use lola\model\AMongoResourceQuery;



abstract class AMongoResourceOperation
{
	
	const VERSION = '0.2.2';
	
	
	
	private $_collection = null;
	
	
	public function __construct(Collection $collection) {
		$this->_collection = $collection;
	}
	
	
	public function getNum(AMongoResourceQuery $query) {
		return $this->_collection->count($query->getQuery());
	}
	
	
	public function has(AMongoResourceQuery $query) {
		return (bool) $this->_collection->count($query->getQuery());
	}
	
	
	public function delete(AMongoResourceQuery $query, $single) {
		if (!is_bool($single)) throw new \ErrorException();
		
		if ($single) $res = $this->_collection->deleteOne($query->getQuery());
		else $res = $this->_collection->deleteMany($query->getQuery());
		
		return $res->getDeletedCount();
	}
}
