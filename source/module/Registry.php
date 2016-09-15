<?php

namespace lola\module;

use lola\app\IApp;

use lola\module\IModule;



class Registry {

	const VERSION = '0.3.0';



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


	public function resolve($type, $hash) {
		if (!is_string($hash) || empty($hash)) throw new \ErrorException();

		$mindex = strpos($hash, ':');

		if ($mindex === false) $mindex = -1;

		$nindex = strpos($hash, '.', $mindex + 1);

		if ($nindex === false) $nindex = strlen($hash);

		$nlen = $nindex - $mindex - 1;

		if ($nlen === 0) throw new \ErrorException('MOD: no name');

		$name = substr($hash, $mindex + 1, $nlen);
		$id = substr($hash, $nindex + 1);

		if ($mindex === -1) return $this->_locateEntity($type, $name, $id);

		$module = substr($hash, 0, $mindex);

		return $this->_produceEntity($module, $type, $name, $id);
	}
}
