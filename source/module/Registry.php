<?php

namespace lola\module;

use lola\app\IApp;

use lola\module\IModule;



class Registry {

	const VERSION = '0.3.1';



	private $_app = null;
	private $_injector = null;
	
	private $_defered = null;
	private $_modules = null;


	public function __construct(IApp& $app) {
		$this->_app =& $app;
		$this->_injector = null;
		
		$this->_defered = [];
		$this->_modules = [];
	}


	private function& _useInjector() {
		if (is_null($this->_injector)) $this->_injector =& $this->_app->useInjector();

		return $this->_injector;
	}
	
	
	private function _getClassPath($moduleName, $entityType, $entityName) {
		if (!array_key_exists($moduleName, $this->_modules)) $this->_loadModule($moduleName);
		
		$module = $this->_modules[$moduleName];
		$locatorConfig = array_key_exists('locator', $module) ? $module['locator'] : [];
		$config = array_key_exists($entityType, $locatorConfig) ? $locatorConfig[$entityType] : [];
		
		$path = array_key_exists('path', $config) ? $config['path'] : $entityType;
		$pre = array_key_exists('prefix', $config) ? $config['prefix'] : '';
		$post = array_key_exists('postfix', $config) ? $config['postfix'] : ucfirst($entityType);
		
		return '\\' . $moduleName . '\\' . $path . '\\' . ucfirst($pre . $entityName . $post);
	}


	private function _locateEntity($entityType, $entityName, $entityId) {
		if (!empty($this->_defered)) $this->_loadDefered();
		
		foreach ($this->_modules as $moduleName => $module) {
			$qname = $this->_getClassPath($moduleName, $entityType, $entityName);
			
			if (class_exists($qname)) return $this->_useInjector()->produce($qname, [ 'id' => $entityId ]);
		}
		
		throw new \ErrorException('MOD: entity missing: ' . $entityType . '|' . $entityName);
	}

	private function _produceEntity($moduleName, $entityType, $entityName, $entityId) {
		$qname = $this->_getClassPath($moduleName, $entityType, $entityName);
		
		if (class_exists($qname)) return $this->_useInjector()->produce($qname, [ 'id' => $entityId ]);
		
		throw new \ErrorException('MOD: entity missing: ' . $entityType . '|' . $entityName);
	}
	
	
	private function _loadModule($name) {
		$qname = '\\' . $name . '\\' . 'Module';

		$loader = $this->_useInjector()->produce($qname);

		if (!($loader instanceof IModule)) throw new \ErrorException('MOD: no loader');

		$this->_modules[$name] = $loader->getModuleConfig();
	}
	
	private function _loadDefered() {
		foreach ($this->_defered as $name) {
			if (!array_key_exists($name, $this->_modules)) $this->_loadModule($name);
		}
		
		$this->_defered = [];
	}
	


	public function loadModule($name) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();
		
		$this->_defered[] = $name;
		
		return $this;
	}

	public function injectModule($name, $module) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();

		$this->_modules[$name] = $module;
		
		return $this;
	}
	
	
	public function parseHash($hash) {
		if (!is_string($hash) || empty($hash)) throw new \ErrorException();
		
		$segs = parse_url($hash);
		
		if ($segs === false) throw new \ErrorException('MOD: hash malformed - ' . $hash);

		$type = array_key_exists('scheme', $segs) ? $segs['scheme'] : '';		
		$module = array_key_exists('host', $segs) ? $segs['host'] : '';
		$name = array_key_exists('path', $segs) ? $name = str_replace('/', '\\', trim($segs['path'], '/')) : '';
		$id = array_key_exists('query', $segs) ? $segs['query'] : '';
		
		return [
			'module' => $module,
			'type' => $type,
			'name' => $name,
			'id' => $id
		];
	}


	public function resolve($type, $hash) {
		$segs = $this->parseHash($hash);
		
		return $this->produce($type, $segs['name'], $segs['id'], $segs['module']);
	}
	
	
	public function produce($type, $name, $id = '', $module = '') {
		if (
			!is_string($type) || empty($type) ||
			!is_string($name) || empty($name) ||
			!is_string($id) || !is_string($module)
		) throw new \ErrorException();
		
		if (empty($module)) return $this->_locateEntity($type, $name, $id);
		else return $this->_produceEntity($module, $type, $name, $id);
	}
}
