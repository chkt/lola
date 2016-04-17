<?php

namespace lola\prov;

use lola\prov\AProvider;
use lola\inject\Injector;
use lola\inject\IInjectable;



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
