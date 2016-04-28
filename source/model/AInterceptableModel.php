<?php

namespace lola\model;

use lola\model\ModelInterceptor;



abstract class AInterceptableModel
extends AModel
{
	
	const VERSION = '0.1.7';
	
	
	
	private $_update = null;
	private $_delete = null;
	private $_interceptor = null;
	
	
	protected function _updateResource(array $data) {
		if (!is_null($this->_interceptor)) $this->_update->process($data);
		
		return parent::_updateResource($data);		
	}
	
	protected function _deleteResource() {
		if (!is_null($this->_interceptor)) $this->_delete->process();
		
		return parent::_deleteResource();
	}
	
	
	public function& useInterceptor() {
		if (is_null($this->_interceptor)) {
			$this->_update = new ModelInterceptorQueue();
			$this->_delete = new ModelInterceptorQueue();
			
			$this->_interceptor = new ModelInterceptor(
				function() {
					return $this->_useResource();
				},
				$this->_update,
				$this->_delete
			);
		}
		
		return $this->_interceptor;
	}
}
