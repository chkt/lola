<?php

namespace lola\app;

use eve\common\access\ItemAccessor;
use eve\common\access\ITraversableAccessor;
use eve\driver\IInjectorDriver;
use eve\inject\IInjectableIdentity;
use eve\inject\IInjector;
use eve\provide\ILocator;
use lola\common\IComponentConfig;



class App
extends ItemAccessor
implements IApp
{

	static public function getDependencyConfig(ITraversableAccessor $config) : array {
		return [[
			'type' => IInjector::TYPE_ARGUMENT,
			'data' => $config->getItem('driver')
		], [
			'type' => IInjector::TYPE_ARGUMENT,
			'data' => $config->getItem('component')
		]];
	}

	static public function getInstanceIdentity(ITraversableAccessor $config) : string {
		return IInjectableIdentity::IDENTITY_SINGLE;
	}



	private $_data;


	public function __construct(IInjectorDriver $driver, IComponentConfig $config) {
		$this->_data = [
			'injector' => $driver->getInjector(),
			'locator' => $driver->getLocator(),
			'config' => $config
		];

		parent::__construct($this->_data);
	}


	public function getInjector() : IInjector {
		return $this->_data['injector'];
	}

	public function getLocator() : ILocator {
		return $this->_data['locator'];
	}


	public function getConfig() : IComponentConfig {
		return $this->_data['config'];
	}
}
