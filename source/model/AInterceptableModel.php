<?php

namespace lola\model;

use lola\model\AActionModel;

use lola\model\ModelInterceptor;
use lola\model\IModelInterceptResolver;



abstract class AInterceptableModel
extends AActionModel
{
	
	const VERSION = '0.2.4';
	
	
	
	private $_interceptor = null;
	
	
	public function& useInterceptor() {
		if (is_null($this->_interceptor)) {
						
			$this->_interceptor = new ModelInterceptor(
				function(IModelInterceptResolver $resolver) {
					$resolver->link($this);
				},
				function() {
					return $this->_useResource()->toArray();		//LEGACY should return Structured data instead of array
				},
				$this->_useUpdateQueue(),
				$this->_useDeleteQueue()
			);
		}
		
		return $this->_interceptor;
	}
}
