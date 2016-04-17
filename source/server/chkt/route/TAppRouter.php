<?php

namespace lola\route;

//REVIEW due to a bug in PHP we cannot use the same trait multiple times
//Until the fix we have to magically assume the existance of methods
//use lola\inject\TAppInjector;



trait TAppRouter {
	//use TAppInjector
	
	
	
	private $_tRouter = null;
	
	
	public function& createRouter($path) {
		$this->_tRouter = $this->useInjector()->produce('\\lola\\route\\CSVRouter', [
			'path' => $path
		]);
		
		return $this->_tRouter;
	}
	
	public function& useRouter() {
		if (is_null($this->_tRouter)) throw new \ErrorException();
		
		return $this->_tRouter;
	}
}
