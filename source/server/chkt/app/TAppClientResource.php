<?php

namespace chkt\app;

//REVIEW due to a bug in PHP we cannot use the same trait multiple times
//Until the fix we have to magically assume the existance of methods
//use \chkt\app\TAppBase;
//use \chkt\app\TAppFile;



trait TAppClientResource {
//	use TAppBase;
//	use TAppFile;
	
	
	
	protected $_dict = [];
	
	protected $_tClientResourceHash = '';
	
	
	
	public function getClientResource($name) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();
		
		if (empty($this->_tClientResourceHash)) {			
			$path = $this->getPath('resource') . '/clientTS';
			$handle = fopen($path, 'r');
			
			if ($handle === false) $this->_tClientResourceHash = md5(time());
			else {
				$this->_tClientResourceHash = fread($handle, 32);
				
				fclose($handle);
			}
		}
		
		return $name . '?' . $this->_tClientResourceHash;
	}
}
