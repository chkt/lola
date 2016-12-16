<?php

namespace lola\prov;

use lola\prov\AProvider;
use lola\inject\IInjectable;
use lola\app\IApp;



class EnvironmentProvider
extends AProvider
implements IInjectable
{

	const VERSION = '0.5.2';


	static public function getDependencyConfig(array $config) {
		return [ 'resolve:app' ];
	}


	private $_systems;


	public function __construct(IApp& $app) {
		parent::__construct(function($name) use (& $app) {
			if (!array_key_exists($name, $this->_systems)) throw new \ErrorException();

			return $app
				->useInjector()
				->produce($this->_systems[$name]);
		});

		$defaults = [
			'registry' => \lola\module\Registry::class,
			'http' => \lola\io\http\HttpDriver::class,
			'log' => \lola\log\FileLogger::class
		];

		$map = $app->hasProperty(IApp::PROP_ENVIRONMENT) ? $app->getProperty(IApp::PROP_ENVIRONMENT) : [];

		$this->_systems = array_merge($defaults, $map);
	}
}
