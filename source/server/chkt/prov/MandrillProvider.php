<?php

namespace chkt\prov;

use \chkt\prov\AProvider;

use \chkt\app\App;

use \Mandrill;



class MandrillProvider extends AProvider {
	
	public function __construct(App $app) {
		parent::__construct(function($id) use ($app) {
			$props = $app->getProperty('mandrill');
			
			if (!array_key_exists($id, $props)) throw new \ErrorException();
			
			return new Mandrill($props[$id]['key']);
		});
	}
}