<?php

namespace chkt\prov;

use chkt\prov\AProvider;
use chkt\type\IInjectable;

use app\app\App;



class ServiceProvider
extends AProvider
implements IInjectable
{
	
	const VERSION = '0.0.9';
	
	
	static public function getDependencyConfig($id) {
		return [[
			'type' => 'app'
		]];
	}
	
	
	
	public function __construct(App $app) {
		parent::__construct(function($hash) use ($app) {
			$segs = explode('.', $hash);
			$type = $segs[0];
			$id = count($segs) > 1 ? $segs[1] : '';
						
			$qname = '\\app\\service\\' . ucfirst($type) . 'Service';
			
			return $app->InjectorFactory($qname, $id);
		});
	}
}
