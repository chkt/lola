<?php

namespace lola\model;

use lola\model\IResource;

use lola\type\StructuredData;
use lola\model\ProxyResourceDriver;



class ProxyResource
implements IResource
{
	const VERSION = '0.2.4';
	
	
	
	protected $_data = null;
	protected $_dirty = false;
	protected $_life = 0;
	protected $_ops = 0;
	
	protected $_create = null;
	protected $_update = null;
	protected $_delete = null;
	
	
	public function __construct(ProxyResourceDriver& $driver) {		
		$this->_data = null;
		$this->_dirty = false;
		$this->_life = self::STATE_NEW;
		$this->_ops = self::OP_NONE;
		
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
	
	
	public function wasCreated() {
		return $this->_ops & self::OP_CREATE;
	}
	
	public function wasRead() {
		return false;
	}
	
	public function wasUpdated() {
		return $this->_ops & self::OP_UPDATE;
	}
	
	public function wasDeleted() {
		return $this->_ops & self::OP_DELETE;
	}
	
	
	public function getData() {
		return $this->_data;
	}
	
	public function setData(StructuredData $data) {
		if ($this->_life !== self::STATE_LIVE) throw new \ErrorException();
		
		$this->_data = $data;
		$this->_dirty = true;
		
		return $this;
	}
	
	
	public function create(StructuredData $data) {
		if ($this->_life !== self::STATE_NEW) throw new \ErrorException();
		
		$this->_data = $data;
		$this->_life = self::STATE_LIVE;
		$this->_ops = self::OP_CREATE;
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
		$this->_ops |= self::OP_UPDATE;

		$this->_update->process($this->_data);
		
		return $this;
	}
	
	public function delete() {
		if ($this->_life !== self::STATE_LIVE) throw new \ErrorException();
				
		$this->_dirty = false;
		$this->_life = self::STATE_DEAD;
		$this->_ops |= self::OP_DELETE;

		$this->_delete->process();
		
		return $this;
	}
}
