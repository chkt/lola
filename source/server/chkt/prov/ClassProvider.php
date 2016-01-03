<?php

namespace chkt\prov;

use chkt\prov\AProvider;
use chkt\inject\Injector;
use chkt\inject\IInjectable;



class ClassProvider
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
		parent::__construct(function($qName) use ($injector) {
			return $injector->produce($qName);
		});
	}
}
