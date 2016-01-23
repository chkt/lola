<?php

namespace chkt\model;

use chkt\model\IResourceQuery;



abstract class AMongoResourceQuery 
implements IResourceQuery
{
	
	const VERSION = '0.1.2';
	
	
	
	protected $_require = null;
	protected $_query = null;
	
	
	public function __construct(Array $require) {
		$this->_require = $require;
	}
	
	
	abstract protected function _buildQuery();
	
	
	
	public function getRequirements() {
		return $this->_require;
	}
	
	
	public function getQuery() {
		if (is_null($this->_query)) $this->_query = $this->_buildQuery();
		
		return $this->_query;
	}
}
