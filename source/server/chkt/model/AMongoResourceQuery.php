<?php

namespace chkt\model;

use chkt\model\IResourceQuery;



abstract class AMongoResourceQuery 
implements IResourceQuery
{
	
	const VERSION = '0.1.3';
	
	const MODE_NONE = 0;
	const MODE_MATCHING = 1;
	const MODE_AGGREGATION = 2;
	
	
	
	protected $_require = null;
	
	protected $_query = null;
	protected $_queryMode = 0;
	
	
	public function __construct(Array $require) {
		$this->_require = $require;
		
		$this->_query = null;
		$this->_queryMode = self::MODE_MATCHING;
	}
	
	
	abstract protected function _buildQuery();
	
	
	
	public function getRequirements() {
		return $this->_require;
	}
	
	
	public function isMatchingQuery() {
		if (is_null($this->_query)) $this->getQuery();
		
		return $this->_queryMode === self::MODE_MATCHING;
	}
	
	public function isAggregationQuery() {
		if (is_null($this->_query)) $this->getQuery();
		
		return $this->_queryMode === self::MODE_AGGREGATION;
	}
	
	
	public function getQuery() {
		if (is_null($this->_query)) $this->_query = $this->_buildQuery();
		
		return $this->_query;
	}
}
