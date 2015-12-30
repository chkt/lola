<?php

namespace chkt\app;

//REVIEW due to a bug in PHP we cannot use the same trait multiple times
//Until the fix we have to magically assume the existance of methods
//use \chkt\app\TAppBase;
//use \chkt\app\TAppFile;



trait TAppTwig {
//	use TAppBase;
//	use TAppFile;
	
	
	protected $_dict = [];
	
	protected $_tTwigEnv = null;
	
	
	
	public function getTwigEnvironment() {
		if (is_null($this->_tTwigEnv)) {
			$dict  = $this->_dict;
			
			$root  = self::getRootPath();
			$path  = $this->getPath('twig');
			$cache = $this->getPath('cache') . DIRECTORY_SEPARATOR . 'twig';
			
			$debug = $this->isDebug();
			
			require_once $path . '/Autoloader.php';
			
			\Twig_Autoloader::register();
			
			$loader = new \Twig_Loader_Filesystem();
			
			if (array_key_exists('twig-names', $dict) && is_array($dict['twig-names'])) {
				$names = $dict['twig-names'];
				
				foreach ($names as $ns => $path) $loader->addPath($root . $path, $ns);
			}
						
			$env = new \Twig_Environment($loader, [
				'cache'           => $cache,
				'debug'           => $debug,
				'stict_variables' => $debug
			]);
			
			if ($debug) $env->addExtension(new \Twig_Extension_Debug());
			
			$env->addExtension(new \chkt\tmpl\LocalTwigExtension($this));
			
			$this->_tTwigEnv = $env;
		}
		
		return $this->_tTwigEnv;
	}
	
	
	public function getTwigView($alias, $name) {
		return '@' . $alias . DIRECTORY_SEPARATOR . $name . '.twig';
	}
	
	
	public function drawTwigTmpl($path, $context) {
		return $this->getTwigEnvironment()->render($path, $context);
	}
	
	public function drawTwigView($alias, $name, $context) {
		$path = $this->getTwigView($alias, $name);
		
		return $this->getTwigEnvironment()->render($path, $context);
	}
}