<?php

namespace lola\inject;

use lola\inject\Injector;

//REVIEW due to a bug in PHP we cannot use the same trait multiple times
//Until the fix we have to magically assume the existance of methods
//use lola\prov\TAppLocator;



trait TAppInjector {
	//use TAppLocator


	
	private $_tInjector = null;
	

	public function& useInjector() {
		if (is_null($this->_tInjector)) $this->_tInjector = new Injector($this->useLocator(), [
			'app' =>& $this
		]);
		
		return $this->_tInjector;
	}
}
