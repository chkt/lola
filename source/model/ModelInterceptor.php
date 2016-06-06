<?php

namespace lola\model;

use lola\model\ModelActionQueue;



class ModelInterceptor {
	
	const VERSION = '0.2.1';
	
	
	
	private $_link = null;
	private $_get = null;
	private $_update = null;
	private $_delete = null;
	
	private $_resolvers = null;
	
	
	public function __construct(
		callable $link,
		callable $get,
		ModelActionQueue& $update,
		ModelActionQueue& $remove
	) {
		$this->_link = $link;
		$this->_get = $get;
		
		$this->_update =& $update;
		$this->_delete =& $remove;
		
		$this->_resolvers = [];
	}
	
	
	public function getData() {		
		return call_user_func($this->_get);
	}
	
	
	public function hasResolver(IModelInterceptResolver& $resolver) {
		return in_array($resolver, $this->_resolvers);
	}
	
	public function addResolver(IModelInterceptResolver& $resolver) {
		if (in_array($resolver, $this->_resolvers)) throw new \ErrorException();
		
		$this->_update->add([$resolver, 'update'], ModelActionQueue::PRIO_INTERCEPT);
		$this->_delete->add([$resolver, 'delete'], ModelActionQueue::PRIO_INTERCEPT);
		
		$this->_resolvers[] = $resolver;
		
		call_user_func($this->_link, $resolver);
		
		return $this;
	}
	
	public function removeResolver(IModelInterceptResolver& $resolver) {
		$index = array_search($resolver, $this->_resolvers);
		
		if ($index === false) throw new \ErrorException();
		
		$this->_update->remove([$resolver, 'update']);
		$this->_delete->remove([$resolver, 'delete']);
		
		unset($this->_resolvers[$index]);
		
		return $this;
	}
}
