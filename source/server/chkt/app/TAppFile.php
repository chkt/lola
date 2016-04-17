<?php

namespace lola\app;



trait TAppFile {

	protected $_dict = [];
	
	protected $_tFileRoot = null;
	protected $_tFilePath = [];	

	
	
	public function getRootPath() {
		if (is_null($this->_tFileRoot)) {
			if (!array_key_exists('rootPath', $this->_dict)) throw new \ErrorException();
			
			$this->_tFileRoot = $this->_dict['rootPath'];
		}
		
		return $this->_tFileRoot;
	}
	
	
	public function getPath($id) {
		$path =& $this->_tFilePath;
		
		if (!array_key_exists($id, $path)) {
			if (!array_key_exists('path', $this->_dict)) throw new \ErrorException();
			
			$root = $this->getRootPath();
			$dict = $this->_dict['path'];
			
			if (!array_key_exists($id, $dict)) throw new \ErrorException();
			
			$path[$id] = $root . $dict[$id];
		}
		
		return $path[$id];
	}
}