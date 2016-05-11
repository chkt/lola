<?php

namespace lola\model;

use lola\model\IResource;

use lola\model\ProxyResourceDriver;



class ProxyResource
implements IResource
{
	const VERSION = '0.1.8';
	
	const STATE_NEW = 1;
	const STATE_LIVE = 2;
	const STATE_DEAD = 3;
	
	
	
	protected $_data = null;
	protected $_dirty = false;
	protected $_life = 0;
	
	protected $_create = null;
	protected $_update = null;
	protected $_delete = null;
	
	
	public function __construct(ProxyResourceDriver& $driver) {		
		$this->_data = null;
		$this->_dirty = false;
		$this->_life = self::STATE_NEW;
		
		$this->_create = new ProxyResourceQueue();
		$this->_update = new ProxyResourceQueue();
		$this->_delete = new ProxyResourceQueue();
		
		$driver->register(
			$this,
			$this->_create,
			$this->_update,
			$this->_delete
		);
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
		
		$this->_data = $data;
		$this->_life = self::STATE_LIVE;
		$this->_dirty = false;
		
		$this->_create->process($data);
		
		return $this;
	}
	
	public function read(IResourceQuery $query) {
		throw new \ErrorException();
	}
	
	public function update() {
		if ($this->_life !== self::STATE_LIVE) throw new \ErrorException();
		
		if (!$this->_dirty) return $this;
		
		$this->_dirty = false;

		$this->_update->process($this->_data);
		
		return $this;
	}
	
	public function delete() {
		if ($this->_life !== self::STATE_LIVE) throw new \ErrorException();
				
		$this->_dirty = false;
		$this->_life = self::STATE_DEAD;

		$this->_delete->process();
		
		return $this;
	}
}
