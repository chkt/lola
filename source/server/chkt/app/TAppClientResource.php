<?php

namespace lola\app;

//REVIEW due to a bug in PHP we cannot use the same trait multiple times
//Until the fix we have to magically assume the existance of methods
//use \lola\app\TAppBase;
//use \lola\app\TAppFile;



trait TAppClientResource {
//	use TAppBase;
//	use TAppFile;
	
	
	
	protected $_dict = [];
	
	protected $_tClientResourceHash = '';
	
	
	private function _readClientResourceTimestamp($path) {
		$handle = @fopen($path, 'r');
		$hash = false;
		
		if ($handle !== false) {
			$hash = fread($handle, 32);
			
			fclose($handle);
		}
		
		return $hash;
	}
	
	private function _createClientResourceTimestamp($path) {
		$handle = fopen($path, 'w');
		$hash = md5(time());
		
		if ($handle !== false) {
			fwrite($handle, $hash);
			fclose($handle);
		}
		
		return $hash;
	}
	
	
	public function getClientResource($name) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();
		
		if (empty($this->_tClientResourceHash)) {			
			$path = $this->getPath('cache') . DIRECTORY_SEPARATOR . 'timestamp';
			$hash = $this->_readClientResourceTimestamp($path);
			
			if ($hash === false) $hash = $this->_createClientResourceTimestamp($path);
			
			$this->_tClientResourceHash = $hash;
		}
		
		return $name . '?' . $this->_tClientResourceHash;
	}
}
