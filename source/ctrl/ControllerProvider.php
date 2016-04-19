<?php

namespace lola\ctrl;

use lola\prov\AProvider;

use lola\inject\Injector;
use lola\inject\IInjectable;

use lola\prov\StackProviderResolver;



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
