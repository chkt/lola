<?php

namespace lola\inject;



interface IInjector
{

	const TYPE_INJECTOR = 'injector';
	const TYPE_LOCATOR = 'locator';
	const TYPE_ENVIRONMENT = 'environment';
	const TYPE_CONTROLLER = 'controller';
	const TYPE_SERVICE = 'service';
	const TYPE_FACTORY = 'factory';
	const TYPE_RESOLVE = 'resolve';
	const TYPE_ARGUMENT = 'object';

	

	public function produce(string $className, array $params = []);

	public function process(callable $fn, array $deps = []);
}
