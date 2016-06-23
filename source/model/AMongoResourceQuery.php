<?php

namespace lola\model;

use lola\model\IResourceQuery;



abstract class AMongoResourceQuery 
implements IResourceQuery
{
	
	const VERSION = '0.2.3';
	
	const MODE_NONE = 0;
	const MODE_MATCHING = 1;
	const MODE_AGGREGATION = 2;
	
	const ORDER_ASC = 1;
	const ORDER_DESC = -1;
	
	
	
	private $_require = null;
	private $_order = null;
	
	protected $_query = null;
	protected $_queryMode = 0;
	protected $_sorting = null;
	
	
	public function __construct(array $require, array $sort = []) {
		$this->_require = $require;
		$this->_order = $sort;
		
		$this->_query = null;
		$this->_queryMode = self::MODE_MATCHING;
		$this->_sorting = null;
	}
	
	
	protected function _getSortingDirection($dir) {
		$map = [
			self::ORDER_ASC => 1,
			self::ORDER_DESC => -1
		];
		
		if (!in_array($dir, $map)) throw new \ErrorException();
		
		return $map[$dir];
	}
	
	
	protected function _buildQuery(Array $require, Array& $match, Array& $aggregate) {
		return null;
	}
	
	
	protected function _buildSorting(array $order, array& $sorting) {
		$sorting['_id'] = self::ORDER_ASC;
	}
	
	
		
	public function getRequirements() {
		return $this->_require;
	}
	
	public function getOrder() {
		return $this->_order;
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
	
	
	public function getSorting() {
		if (is_null($this->_sorting)) {
			$this->_sorting = [];
			$this->_buildSorting($this->_order, $this->_sorting);
		}
		
		return $this->_sorting;
	}
}
