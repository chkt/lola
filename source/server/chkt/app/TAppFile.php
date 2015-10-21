<?php

namespace chkt\app;



trait TAppFile {
	
	static protected $_tFileRoot = null;
	

	protected $_dict = [];
	
	protected $_tFilePath = [];	
	
	
	static private function _getRootPath() {
		$path = filter_input(INPUT_SERVER, 'SCRIPT_FILENAME');
		$file = filter_input(INPUT_SERVER, 'SCRIPT_NAME');
		
		return substr($path, 0, strpos($path, $file));
	}
	
	static public function getRootPath() {
		if (is_null(self::$_tFileRoot)) self::$_tFileRoot = self::_getRootPath();
		
		return self::$_tFileRoot;
	}
	
	static public function registerNamespace(Array $names) {
		$root = self::_getRootPath();
		
		spl_autoload_register(function($name) use ($root, $names) {
			$segs = explode('\\', $name);
			$ns   = array_shift($segs);
						
			foreach ($names as $name => $path) {
				if ($name !== $ns) continue;
				
				include $root . $path . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $segs) . '.php';
								
				return true;
			}
						
			return false;
		}, true);
	}
	
	
	
	public function getPath($id) {
		$path = $this->_tFilePath;
		
		if (!array_key_exists($id, $path)) {
			if (!array_key_exists('path', $this->_dict)) throw new \ErrorException();
			
			$dict = $this->_dict['path'];
			$root = self::getRootPath();
			
			if (!array_key_exists($id, $dict)) throw new \ErrorException();
			
			$path[$id] = $root . $dict[$id];
		}
		
		return $path[$id];
	}
}