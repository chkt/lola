<?php

namespace lola\service;

use lola\prov\AProvider;
use lola\inject\IInjectable;
use lola\module\EntityParser;
use lola\module\Registry;



final class ServiceProvider
extends AProvider
implements IInjectable
{

	const VERSION = '0.5.2';


	static public function getDependencyConfig(Array $config) {
		return [ 'environment:registry' ];
	}



	public function __construct(Registry& $registry) {
		parent::__construct(function($hash) use ($registry) {
			$segs = EntityParser::parse($hash);

			return $registry->produce('service', $segs['name'], !empty($segs['id']) ? $segs['id'] : 'default', $segs['module']);
		});
	}
}
