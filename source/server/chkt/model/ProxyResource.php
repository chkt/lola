<?php

namespace chkt\model;

use chkt\model\IResource;

use chkt\model\ProxyResourceDriver;



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
	
	protected $_driver = null;
	
	
	public function __construct(ProxyResourceDriver& $driver) {		
		$this->_data = null;
		$this->_dirty = false;
		$this->_life = self::STATE_NEW;
		
		$this->_driver =& $driver;
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
		
		$this->_driver->dispatch($this, ProxyResourceDriver::ACTION_CREATE);
		
		return $this;
	}
	
	public function read(IResourceQuery $query) {
		throw new \ErrorException();
	}
	
	public function update() {
		if ($this->_life !== self::STATE_LIVE) throw new \ErrorException();
		
		if (!$this->_dirty) return $this;
		
		$this->_dirty = false;
		
		$this->_driver->dispatch($this, ProxyResourceDriver::ACTION_UPDATE);
		
		return $this;
	}
	
	public function delete() {
		if ($this->_life !== self::STATE_LIVE) throw new \ErrorException();
				
		$this->_dirty = false;
		$this->_life = self::STATE_DEAD;
		
		$this->_driver->dispatch($this, ProxyResourceDriver::ACTION_DELETE);
		
		return $this;
	}
}
