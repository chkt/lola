<?php

namespace chkt\prov;

use chkt\prov\AProvider;

use chkt\app\IApp;



class LoggerProvider extends AProvider {
	
	public function __construct(IApp $app) {
		parent::__construct(function($id) use ($app) {
			$props = $app->getProperty('log');
			
			if (!array_key_exists($id, $props)) {
				$name = '\\chkt\\log\\FileLogger';
			}
			else {
				$config = $props[$id];
				$name = (array_key_exists('ns', $config) ? 'ns' : '\\chkt\log\\') . $config['name'];
			}
			
			$ins = new $name();
			
			return $ins;
		});
	}
}
