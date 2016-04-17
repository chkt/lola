<?php

namespace lola\model;

use lola\inject\IDependencyFactory;
use lola\inject\Injector;

use lola\model\IResource;



abstract class AResourceDependencyFactory
implements IDependencyFactory
{
	
	const MODE_NONE = 0;
	const MODE_CREATE = 1;
	const MODE_READ = 2;
	const MODE_PASS = 3;
	
	
	
	static public function getDependencyConfig(Array $config) {
		return [[
			'type' => Injector::TYPE_INJECTOR
		]];
	}
	
	
	
	protected $_injector = null;
	protected $_mode = self::MODE_NONE;
	
	protected $_model = '';
	protected $_resource = '';
	protected $_query = '';
		
	protected $_config = null;
	protected $_instance = null;
	
	
	public function __construct(Injector& $injector, $model, $resource, $query) {
		$this->_injector =& $injector;
		
		$this->_mode = self::MODE_NONE;
		
		$this->_model = $model;
		$this->_resource = $resource;
		$this->_query = $query;
				
		$this->_config = null;
		$this->_instance = null;
	}
	
	
	protected function _getCreateDependencies(Array $config) {
		return [];
	}
	
	
	protected function _produceProxy() {
		if (
			!array_key_exists('resource', $this->_config) ||
			!($this->_config['resource'] instanceof IResource)
		) throw new \ErrorException();
		
		return $this->_config['resource'];
	}
	
	protected function _produceCreate() {
		$deps = $this->_getCreateDependencies($this->_config);
		$data = $this->_injector->process([$this->_model, 'produceModel'], $deps);

		return $this->_injector
			->produce($this->_resource)
			->create($data);
	}
	
	protected function _produceRead() {
		if (!array_key_exists('map', $this->_config)) throw new \ErrorException();
		
		return $this->_injector
			->produce($this->_resource)
			->read(new $this->_query($this->_config['map']));
	}
	
	
	public function setConfig(Array $config) {
		$mode = array_key_exists('mode', $config) ? $config['mode'] : self::MODE_READ;
		
		$this->_mode = $mode;
		
		$this->_config = $config;
		$this->_instance = null;
		
		return $this;
	}
	
	
	public function produce() {
		if (!is_null($this->_instance)) return $this->_instance;
		
		switch ($this->_mode) {
			case self::MODE_PASS : return $this->_produceProxy();
			case self::MODE_READ : return $this->_produceRead();
			case self::MODE_CREATE : return $this->_produceCreate();
			default : throw new \ErrorException();
		}
	}
}
