<?php

namespace lola\app;


interface IApp {

	const PROP_LOCATOR = 'locator';
	const PROP_ENVIRONMENT = 'environment';



	public function& useInjector() : Injector;

	public function& useLocator();

	public function& useRegistry();


	public function hasProperty(string $name) : bool;

	public function getProperty(string $name);
}
