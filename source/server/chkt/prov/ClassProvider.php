<?php

namespace chkt\prov;

use chkt\prov\AProvider;
use chkt\type\IInjectable;

use app\app\App;



class ClassProvider
extends AProvider
implements IInjectable {
	
	const VERSION = '0.0.9';
	
	
	static public function getDependencyConfig($id) {
		return [[
			'type' => 'app'
		]];
	}
	
	
	
	public function __construct(App $app) {
		parent::__construct(function($qName) use ($app) {
			return $app->InjectorFactory($qName);
		});
	}
}
