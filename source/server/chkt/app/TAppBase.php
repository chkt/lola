<?php

namespace chkt\app;

use chkt\http\HttpReply;
use chkt\http\Cookie;



trait TAppBase {
	
	/**
	 * The configuration dictionary
	 * @var array
	 */
	protected $_dict = [];
	
	/**
	 * DEPRECATED
	 * The synced app timestamp
	 * @var integer
	 */
	protected $_now   = null;
	/**
	 * The debug flag
	 * @var boolean
	 */
	protected $_debug = null;
	
	
	
	/**
	 * Registers the response for uncaught exceptions
	 * @param string $path The exception response file path
	 */
	static public function registerExceptionPage($path) {
		register_shutdown_function(function($path) {
			$error = error_get_last();
			
			if (is_null($error)) return;
			
			switch ($error['type']) {
				case E_PARSE :
				case E_COMPILE_ERROR :
				case E_ERROR :
				case E_RECOVERABLE_ERROR :
					ob_start();
					
					include $path;
					
					HttpReply::OB(500, HttpReply::MIME_HTML);
			}
		}, $path);
	}
	
	
	/**
	 * Returns <code>true</code> is the app is in debug mode, <code>false</code> otherwise
	 * @return boolean
	 */
	public function isDebug() {
		if (is_null($this->_debug)) {
			$dict = $this->_dict;
			
			if (array_key_exists('debug', $dict)) $this->_debug = (boolean) $dict['debug'];
			else $this->_debug = false;
		}
		
		return $this->_debug;
	}
	
	/**
	 * Updates <code>$cookie</code> with the current debug mode of the app
	 * @param Cookie $cookie The cookie representation
	 * @return boolean
	 */
	public function updateDebug(Cookie &$cookie, $exclude = false) {
		$debug = $this->isDebug();
		
		if ($debug) {
			$cookie
				->set('debug', '1', Cookie::EXPIRES_SESSION, Cookie::PATH_ROOT)
				->set('t', '1', Cookie::EXPIRES_SESSION, Cookie::PATH_ROOT);
		}
		else { 
			$cookie->reset('debug');
			
			if ($exclude) $cookie->set('t', '1', Cookie::EXPIRES_SESSION, Cookie::PATH_ROOT);
		}
		
		return [
			'debug' => $debug,
			'exclude' => $cookie->value('t')
		];
	}
}