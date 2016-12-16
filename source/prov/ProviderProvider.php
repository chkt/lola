<?php

namespace lola\prov;

use lola\prov\AProvider;

use lola\app\IApp;



class ProviderProvider
extends AProvider
{

	const VERSION = '0.5.4';



	private $_providers;


	public function __construct(IApp $app) {
		parent::__construct(function($providerName) use (& $app) {
			if (!array_key_exists($providerName, $this->_providers)) throw new \ErrorException();

			return $app
				->useInjector()
				->produce($this->_providers[$providerName]);
		});

		$defaults = [
			'environment' => \lola\prov\EnvironmentProvider::class,
			'service' => \lola\service\ServiceProvider::class,
			'controller' => \lola\ctrl\ControllerProvider::class,
			'class' => \lola\prov\ClassProvider::class
		];

		$map = $app->hasProperty(IApp::PROP_LOCATOR) ? $app->getProperty(IApp::PROP_LOCATOR) : [];

		$this->_providers = array_merge($defaults, $map);
	}


	public function& locate(string $type, string $location) {
		return $this
			->using($type)
			->using($location);
	}
}
