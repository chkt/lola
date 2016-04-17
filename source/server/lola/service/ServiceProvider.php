<?php

namespace lola\service;

use lola\prov\AProvider;
use lola\inject\Injector;
use lola\inject\IInjectable;



final class ServiceProvider
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
		parent::__construct(function($hash) use ($injector) {
			if (!is_string($hash) || empty($hash)) throw new \ErrorException();
			
			$segs = explode('.', $hash);
			$name = $segs[0];
			$id = count($segs) > 1 ? implode('.', array_slice($segs, 1)) : 'default';
						
			$qname = '\\app\\service\\' . ucfirst($name) . 'Service';
			
			return $injector->produce($qname, [
				'id' => $id
			]);
		});
	}
}
