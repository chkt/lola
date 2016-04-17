<?php

namespace lola\service;

use lola\inject\IInjectable;



abstract class AService implements IInjectable {
	
	static public function getDependencyConfig(Array $config) {
		return [];
	}
}
