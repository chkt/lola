<?php

namespace chkt\service;

use chkt\inject\IInjectable;



abstract class AService implements IInjectable {
	
	static public function getDependencyConfig(Array $config) {
		return [];
	}
}
