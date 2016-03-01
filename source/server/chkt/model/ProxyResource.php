<?php

namespace chkt\model;

use chkt\model\IResource;



class ProxyResource
implements IResource
{
	const VERSION = '0.1.4';
	
	const STATE_NEW = 1;
	const STATE_LIVE = 2;
	const STATE_DEAD = 3;
	
	
	
	protected $_data = null;
	protected $_dirty = false;
	protected $_life = 0;
	
	protected $_update = null;
	protected $_delete = null;
	
	
	public function __construct(Callable $update = null, Callable $delete = null) {		
		$this->_data = null;
		$this->_dirty = false;
		$this->_life = self::STATE_NEW;
		
		$this->_update = $update;
		$this->_delete = $delete;
	}
	
	
	public function isLive() {
		return $this->_life === self::STATE_LIVE;
	}
	
	public function isDirty() {
		return $this->_dirty;
	}
	
	
	public function getData() {
		return $this->_data;
	}
	
	public function setData(Array $data) {
		if ($this->_life !== self::STATE_LIVE) throw new \ErrorException();
		
		$this->_data = $data;
		$this->_dirty = true;
		
		return $this;
	}
	
	
	public function create(Array $data) {
		if ($this->_life !== self::STATE_NEW) throw new \ErrorException();
		
		$this->_data =& $data;
		$this->_life = self::STATE_LIVE;
		$this->_dirty = false;
		
		return $this;
	}
	
	public function read(IResourceQuery $query) {
		throw new \ErrorException();
	}
	
	public function update() {
		if ($this->_life !== self::STATE_LIVE || is_null($this->_update)) throw new \ErrorException();
		
		if (!$this->_dirty) return $this;
		
		call_user_func($this->_update, $this->_data);
		
		$this->_dirty = false;
		
		return $this;
	}
	
	public function delete() {
		if ($this->_life !== self::STATE_LIVE || is_null($this->_delete)) throw new \ErrorException();
		
		call_user_func($this->_delete, $this->_data);
		
		$this->_dirty = false;
		$this->_life = self::STATE_DEAD;
		
		return $this;
	}
}
