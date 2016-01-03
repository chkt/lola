<?php

namespace chkt\prov;

use chkt\prov\AProvider;

use chkt\app\IApp;



class ProviderProvider extends AProvider {
	
	const VERSION = '0.1.0';
	
	
	
	public function __construct(IApp $app) {
		parent::__construct(function($providerName) use ($app) {
			$props = $app->getProperty('locator');
			
			if (!array_key_exists($providerName, $props)) throw new \ErrorException();
			
			$qName = $props[$providerName];
			
			return $app
				->useInjector()
				->produce($qName);
		});
	}
}
