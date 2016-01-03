<?php

namespace chkt\ctrl;

use chkt\prov\AProvider;

use chkt\inject\Injector;
use chkt\inject\IInjectable;

use chkt\prov\StackProviderResolver;



class ControllerProvider
extends AProvider 
implements IInjectable
{
	
	const VERSION = '0.1.0';
	
	
	static public function getDependencyConfig(Array $config) {
		return [[
			'type' => 'injector'
		]];
	}
	
	
	
	public function __construct(Injector $injector) {
		parent::__construct(function($name) use ($injector) {
			return $injector->produce('\\app\\ctrl\\' . ucfirst($name) . 'Controller');
		}, new StackProviderResolver(StackProviderResolver::RESOLVE_UNIQUE));
	}
}
