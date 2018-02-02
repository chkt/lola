<?php

namespace lola\module;

use eve\common\access\ITraversableAccessor;
use eve\inject\IInjectableIdentity;
use eve\inject\IInjector;
use eve\provide\ILocator;



class Registry
implements IRegistry
{

	static public function getDependencyConfig(ITraversableAccessor $config) : array {
		return [
			'injector:',
			'locator:',
			'core:entityParser'
		];
	}

	static public function getInstanceIdentity(ITraversableAccessor $config) : string {
		return IInjectableIdentity::IDENTITY_SINGLE;
	}



	private $_injector;
	private $_locator;
	private $_parser;

	private $_defered;
	private $_modules;

	private $_dependencyStack;


	public function __construct(IInjector $injector, ILocator $locator, IEntityParser $parser) {
		$this->_injector = $injector;
		$this->_locator = $locator;
		$this->_parser = $parser;

		$this->_defered = [];
		$this->_modules = [];

		$this->_dependencyStack = [];
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

		return '\\' . $moduleName . '\\' . $path . '\\' . $pre . ucfirst($entityName) . $post;
	}


	/**
	 * Locates and returns the qualified name referenced by $entityType and $entityName
	 * @param string $entityType
	 * @param string $entityName
	 * @return string
	 * @throws \ErrorException if the referenced entity does not exist
	 */
	private function _locateQualifiedName(string $entityType, string $entityName) : string {
		if (!empty($this->_defered)) $this->_loadDefered();

		foreach ($this->_modules as $moduleName => $module) {
			$qname = $this->_getClassPath($moduleName, $entityType, $entityName);

			if (class_exists($qname)) return $qname;
		}

		throw new \ErrorException(sprintf(
			'MOD unresolvable "%s:%s"',
			$entityType,
			$entityName
		));
	}

	/**
	 * Returns the qualified name referenced by $moduleName, $entityType and $entityName
	 * @param string $moduleName
	 * @param string $entityType
	 * @param string $entityName
	 * @return string
	 * @throws \ErrorException if the referenced entity does not exist
	 */
	private function _buildQualifiedName(string $moduleName, string $entityType, string $entityName) : string {
		$qname = $this->_getClassPath($moduleName, $entityType, $entityName);

		if (!class_exists($qname)) throw new \ErrorException(sprintf(
			'MOD unresolvable "%s://%s/%s" - missing "%s"',
			$moduleName,
			$entityType,
			$entityName,
			$qname
		));

		return $qname;
	}


	/**
	 * Applies the entity config specified by $config
	 * @param array $config The entity config
	 * @return Registry
	 */
	private function _applyConfig(array $config) : Registry {
		foreach ($config as $hash => $fn) {
			$entity = $this->_parser->parse($hash, EntityParser::COMPONENT_MODULE);

			$this->_locator
				->getItem($entity[EntityParser::COMPONENT_TYPE])
				->addConfiguration($entity[EntityParser::COMPONENT_DESCRIPTOR], $fn);
		}

		return $this;
	}


	/**
	 * Resolves the named dependencies in $deps
	 * @param string $name The module name
	 * @param array $deps The dependencies
	 * @return Registry
	 * @throws \ErrorException if $deps is not an array of nonempty strings
	 * @throws \ErrorException if any string in $deps will create a dependency loop
	 */
	private function _resolveDependencies(string $name, array $deps) : Registry {
		array_push($this->_dependencyStack, $name);

		foreach ($deps as $dep) {
			if (!is_string($dep) || empty($dep)) throw new \ErrorException('MOD: malformed dependency');

			if (array_key_exists($dep, $this->_modules)) continue;

			if (in_array($dep, $this->_dependencyStack)) throw new \ErrorException('MOD: circular dependency');

			$this->_loadModule($dep);
		}

		array_pop($this->_dependencyStack);

		return $this;
	}


	/**
	 * Loads the module referenced by $name
	 * @param string $name The module name
	 * @return IRegistry
	 * @throws \ErrorException if the module referenced by name is not an instance of IModule
	 */
	private function _loadModule(string $name) : IRegistry {
		$qname = '\\' . $name . '\\' . 'Module';

		$loader = $this->_injector->produce($qname);

		if (!($loader instanceof IModule)) throw new \ErrorException('MOD: no loader');

		$module = $loader->getModuleConfig();

		return $this->injectModule($name, $module);
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
	 * @return IRegistry
	 * @throws \ErrorException if $name is not a nonempty string
	 */
	public function loadModule(string $name) : IRegistry {
		if (empty($name)) throw new \ErrorException();

		$this->_defered[] = $name;

		return $this;
	}

	/**
	 * Injects $module to registry
	 * @param string $name The module name
	 * @param array $module The module description
	 * @return IRegistry
	 * @throws \ErrorException if $name is empty
	 */
	public function injectModule(string $name, array $module) : IRegistry {
		if (empty($name)) throw new \ErrorException();

		if (array_key_exists('depend', $module)) {
			$this->_resolveDependencies($name, $module['depend']);

			unset($module['depend']);
		}

		if (array_key_exists('config', $module)) {
			$this->_applyConfig($module['config']);

			unset($module['config']);
		}

		$this->_modules[$name] = $module;

		return $this;
	}


	/**
	 * Returns the qualified name referenced by $type, $name and $module
	 * @param string $type The entity type
	 * @param string $name The entity name
	 * @param string $module The module id
	 * @return string
	 * @throws \ErrorException if $type is not a nonempty string
	 * @throws \ErrorException if $name is not a nonempty string
	 * @throws \ErrorException if $id is not a string
	 * @throws \ErrorException if $module is not a string
	 */
	public function getQualifiedName(string $type, string $name, string $module = '') : string {
		if (empty($type) || empty($name)) throw new \ErrorException();

		if (empty($module)) return $this->_locateQualifiedName($type, $name);
		else return $this->_buildQualifiedName($module, $type, $name);
	}
}
