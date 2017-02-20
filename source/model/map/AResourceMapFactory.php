<?php

namespace lola\model\map;

use lola\inject\IDependencyFactory;
use lola\inject\IInjector;



abstract class AResourceMapFactory
implements IDependencyFactory
{

	const MODE_NONE = 0;
	const MODE_READ = 1;
	const MODE_PASS = 2;


	static public function getDependencyConfig(array $config) {
		return ['injector:'];
	}



	private $_injector;
	private $_resource;

	private $_mode;
	private $_config;
	private $_instance;


	public function __construct(IInjector& $injector, string $resource) {
		$this->_injector =& $injector;
		$this->_resource = $resource;

		$this->_mode = self::MODE_NONE;
		$this->_config = null;
		$this->_instance = null;
	}



	public function setConfig(array $config) {
		$mode = array_key_exists('mode', $config) ? $config['mode'] : self::MODE_READ;

		$this->_mode = $mode;

		$this->_config = $config;
		$this->_instance = null;

		return $this;
	}


	private function _produceProxy() : IResourceMap {
		if (!array_key_exists('resource', $this->_config)) throw new \ErrorException();

		return $this->_config['resource'];
	}

	private function _produceRead() {
		return $this->_injector->produce($this->_resource);
	}


	public function& produce() {
		if (!is_null($this->_instance)) return $this->_instance;

		$mode = $this->_mode;
		$instance =& $this->_instance;

		if ($mode === self::MODE_PASS) $instance = $this->_produceProxy();
		else if ($mode === self::MODE_READ) $instance = $this->_produceRead();
		else throw new \ErrorException();

		return $instance;
	}
}
