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


	/**
	 * @param IApp $app
	 */
	public function __construct(IApp& $app) {
		$this->_app =& $app;
		$this->_injector = null;
		
		$this->_defered = [];
		$this->_modules = [];
	}


	/**
	 * Returns a reference to the injector
	 * @return Injector
	 */
	private function& _useInjector() {
		if (is_null($this->_injector)) $this->_injector =& $this->_app->useInjector();

		return $this->_injector;
	}
	
	
	/**
	 * Returns the classpath referenced by $moduleName, $entityType and $entityName
	 * @param string $moduleName
	 * @param string $entityType
	 * @param string $entityName
	 * @return string
	 */
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


	/**
	 * Locates and returns the entity referenced by $entityType, $entityName and $entityId
	 * @param string $entityType
	 * @param string $entityName
	 * @param string $entityId
	 * @return mixed
	 * @throws \ErrorException if the referenced entity does not exist
	 */
	private function _locateEntity($entityType, $entityName, $entityId) {
		if (!empty($this->_defered)) $this->_loadDefered();
		
		foreach ($this->_modules as $moduleName => $module) {
			$qname = $this->_getClassPath($moduleName, $entityType, $entityName);
			
			if (class_exists($qname)) return $this->_useInjector()->produce($qname, [ 'id' => $entityId ]);
		}
		
		throw new \ErrorException('MOD: entity missing: ' . $entityType . '|' . $entityName);
	}

	/**
	 * Returns the entity referenced by $moduleName, $entityType, $entityName and $entityId
	 * @param string $moduleName
	 * @param string $entityType
	 * @param string $entityName
	 * @param string $entityId
	 * @return mixed
	 * @throws \ErrorException if the referenced entity does not exist
	 */
	private function _produceEntity($moduleName, $entityType, $entityName, $entityId) {
		$qname = $this->_getClassPath($moduleName, $entityType, $entityName);
		
		if (class_exists($qname)) return $this->_useInjector()->produce($qname, [ 'id' => $entityId ]);
		
		throw new \ErrorException('MOD: entity missing: ' . $entityType . '|' . $entityName);
	}
	
	
	/**
	 * Loads the module referenced by $name
	 * @param string $name The module name
	 * @throws \ErrorException if the module referenced by name is not an instance of IModule
	 */
	private function _loadModule($name) {
		$qname = '\\' . $name . '\\' . 'Module';

		$loader = $this->_useInjector()->produce($qname);

		if (!($loader instanceof IModule)) throw new \ErrorException('MOD: no loader');

		$module = $loader->getModuleConfig();
		
		$this->_modules[$name] = $module;
	}
	
	/**
	 * Loads all defered modules
	 */
	private function _loadDefered() {
		$defered = $this->_defered;
		$this->_defered = [];
		
		foreach ($defered as $name) {
			if (!array_key_exists($name, $this->_modules)) $this->_loadModule($name);
		}
	}
	

	/**
	 * Adds the module referenced by $name to the defered modules
	 * @param string $name The module name
	 * @return Registry
	 * @throws \ErrorException if $name is not a nonempty string
	 */
	public function loadModule($name) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();
		
		$this->_defered[] = $name;
		
		return $this;
	}

	/**
	 * Injects $module to registry
	 * @param string $name The module name
	 * @param array $module The module config
	 * @return Registry
	 * @throws \ErrorException if $name is not a nonempty string
	 */
	public function injectModule($name, $module) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();
		
		$this->_modules[$name] = $module;
		
		return $this;
	}
	
	
	/**
	 * Returns the entity definition referenced by hash
	 * @param string $hash
	 * @return array
	 * @throws \ErrorException if $hash is not a nonempty string
	 */
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


	/**
	 * Returns the entity referenced by $type and $hash
	 * @param string $type The entity type
	 * @param string $hash The entity hash
	 * @return mixed
	 */
	public function resolve($type, $hash) {
		$segs = $this->parseHash($hash);
		
		return $this->produce($type, $segs['name'], $segs['id'], $segs['module']);
	}
	
	
	/**
	 * Returns the entity referenced by $type, $name, $id and $module
	 * @param string $type The entity type
	 * @param string $name The entity name
	 * @param string $id The unique entity id
	 * @param string $module The module id
	 * @return mixed
	 * @throws \ErrorException if $type is not a nonempty string
	 * @throws \ErrorException if $name is not a nonempty string
	 * @throws \ErrorException if $id is not a string
	 * @throws \ErrorException if $module is not a string
	 */
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
