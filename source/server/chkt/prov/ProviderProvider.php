<?php

namespace chkt\prov;

use chkt\prov\AProvider;

use chkt\app\App;



class ProviderProvider extends AProvider {
	
	const VERSION = '0.0.9';
	
	
	
	public function __construct(App $app) {
		parent::__construct(function($providerName) use ($app) {
			$props = $app->getProperty('locator');
			
			if (!array_key_exists($providerName, $props)) throw new \ErrorException();
			
			$qName = $props[$providerName];
			
			return $app->InjectorFactory($qName);
		});
	}
}
