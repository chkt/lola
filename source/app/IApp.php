<?php

namespace lola\app;

use lola\inject\IInjector;
use lola\prov\ProviderProvider;



interface IApp {

	const PROP_LOCATOR = 'locator';
	const PROP_ENVIRONMENT = 'environment';



	public function& useInjector() : IInjector;

	public function& useLocator() : ProviderProvider;


	public function hasProperty(string $name) : bool;

	public function getProperty(string $name);
}
