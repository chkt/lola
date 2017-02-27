<?php

namespace lola\model;

use lola\inject\IDependencyFactory;
use lola\inject\Injector;

use lola\model\collection\IResourceCollection;



abstract class AResourceCollectionDependencyFactory
implements IDependencyFactory
{

	const MODE_NONE = 0;
	const MODE_READ = 1;
	const MODE_PASS = 2;



	private $_injector = null;
	private $_mode = self::MODE_NONE;

	private $_resource = '';
	private $_query = '';

	private $_config = null;
	private $_instance = null;


	static public function getDependencyConfig(array $config) {
		return [[
			'type' => Injector::TYPE_INJECTOR
		]];
	}



	public function __construct(Injector& $injector, $resource, $query) {
		$this->_injector =& $injector;

		$this->_mode = self::MODE_NONE;

		$this->_resource = $resource;
		$this->_query = $query;

		$this->_config = null;
		$this->_instance = null;
	}


	protected function _produceRead() {
		$config = $this->_config;

		$map = array_key_exists('map', $config) ? $config['map'] : [];
		$order = array_key_exists('order', $config) ? $config['order'] : [];
		$offset = array_key_exists('limit', $config) ? $config['limit'] : 10;
		$limit = array_key_exists('offset', $config) ? $config['offset'] : 0;

		return $this->_injector
			->produce($this->_resource)
			->read(new $this->_query($map, $order), $offset, $limit);
	}

	protected function _produceProxy() {
		if (
			!array_key_exists('resource', $this->_config) ||
			!($this->_config['resource'] instanceof IResourceCollection)
		) throw new \ErrorException();

		return $this->_config['resource'];
	}


	public function setConfig(array $config) {
		$mode = array_key_exists('mode', $config) ? $config['mode'] : self::MODE_READ;

		$this->_mode = $mode;

		$this->_config = $config;
		$this->_instance = null;

		return $this;
	}


	public function& produce() {
		if (!is_null($this->_instance)) return $this->_instance;

		$mode = $this->_mode;
		$instance = null;

		if ($mode === self::MODE_PASS) $instance = $this->_produceProxy();
		else if ($mode === self::MODE_READ) $instance = $this->_produceRead();
		else throw new \ErrorException();

		$this->_instance =& $instance;

		return $instance;
	}
}
