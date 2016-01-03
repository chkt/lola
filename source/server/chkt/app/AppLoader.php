<?php

namespace chkt\app;



final class AppLoader {
	static private $_ins = null;
	
	
	private $_root = null;
	
	private $_app = null;

	
		
	static public function Init(Array $config = []) {
		if (!is_null(self::$_ins)) throw new \ErrorException();
		
		$ins = new self($config);
		
		self::$_ins = $ins;
		
		return $ins;
	}
	
	
	static private function _getRootPath() {
		$path = filter_input(INPUT_SERVER, 'SCRIPT_FILENAME');
		$file = filter_input(INPUT_SERVER, 'SCRIPT_NAME');
		
		return substr($path, 0, strpos($path, $file));
	}
	
	
	static public function getInstance() {
		if (is_null(self::$_ins)) throw new \ErrorException();
		
		return self::$_ins;
	}
	
	
	
	private function __construct(Array $config = []) {
		if (array_key_exists('rootOffset', $config)) $this->_setRootOffset($config['rootOffset']);
		
		if (array_key_exists('namespace', $config)) $this->registerNamespace($config['namespace']);
		if (array_key_exists('composer', $config)) $this->registerComposerAutoload();
		
		$config['rootPath'] = $this->getRootPath();
		
		$app = $this->_app = new \app\app\App($config);
		
		if (array_key_exists('exceptionPage', $config)) $app->registerExceptionPage($config['rootPath'] . DIRECTORY_SEPARATOR . $config['exceptionPage']);
	}
	
	
	private function _setRootOffset($path) {
		if (!is_string($path)) throw new \ErrorException();
				
		$segs = explode(DIRECTORY_SEPARATOR, $path);
		
		if (empty($segs)) return;
		
		$rsegs = explode(DIRECTORY_SEPARATOR, self::_getRootPath());
		
		foreach($segs as $seg) {
			if ($seg === '..') array_pop($rsegs);
			else if ($seg === '.') continue;
			else array_push($rsegs, $seg);
		}
		
		$this->_root = implode(DIRECTORY_SEPARATOR, $rsegs);
	}
	
	
	public function getRootPath() {
		if (is_null($this->_root)) $this->_root = self::_getRootPath();
		
		return $this->_root;
	}
	
	public function getApp() {
		return $this->_app;
	}

	
	public function registerComposerAutoload() {
		$root = $this->getRootPath();
		
		include $root . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
	}
	
	public function registerNamespace(Array $names) {
		$root = $this->getRootPath();
		
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
}