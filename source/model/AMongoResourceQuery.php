<?php

namespace lola\model;

use lola\model\IResourceQuery;



abstract class AMongoResourceQuery 
implements IResourceQuery
{
	
	const VERSION = '0.1.4';
	
	const MODE_NONE = 0;
	const MODE_MATCHING = 1;
	const MODE_AGGREGATION = 2;
	
	
	
	private $_require = null;
	
	protected $_query = null;
	protected $_queryMode = 0;
	
	
	public function __construct(Array $require) {
		$this->_require = $require;
		
		$this->_query = null;
		$this->_queryMode = self::MODE_MATCHING;
	}
	
	
	abstract protected function _buildQuery(Array $require, Array& $match, Array& $aggregate);
	
	
		
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
		if (is_null($this->_query)) {
			$matches = [];
			$aggregate = [];
			
			$this->_buildQuery($this->_require, $matches, $aggregate);
			
			$hasMatches = !empty($matches);
			$match = $hasMatches ? [ '$and' => $matches ] : [];
			
			if (!empty($aggregate)) {
				if ($hasMatches) array_unshift($aggregate, [ '$match' => $match]);
				
				$this->_query = $aggregate;
				$this->_queryMode = self::MODE_AGGREGATION;
			}
			else {
				$this->_query = $match;
				$this->_queryMode = self::MODE_MATCHING;
			}
		}
		
		return $this->_query;
	}
}