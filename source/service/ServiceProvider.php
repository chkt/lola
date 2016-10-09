<?php

namespace lola\service;

use lola\prov\AProvider;
use lola\inject\Injector;
use lola\inject\IInjectable;
use lola\module\EntityParser;
use lola\module\Registry;



final class ServiceProvider
extends AProvider
implements IInjectable
{

	const VERSION = '0.3.2';


	static public function getDependencyConfig(Array $config) {
		return [[
			'type' => Injector::TYPE_REGISTRY
		]];
	}



	public function __construct(Registry& $registry) {
		parent::__construct(function($hash) use ($registry) {
			$segs = EntityParser::parse($hash);
			
			return $registry->produce('service', $segs['name'], !empty($segs['id']) ? $segs['id'] : 'default', $segs['module']);
		});
	}
}
