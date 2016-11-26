<?php

namespace lola\app;

use lola\http\HttpReply;



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

					$driver = new \lola\io\http\HttpDriver();
					$driver
						->useReply()
						->setCode(\lola\io\http\HttpConfig::CODE_ERROR)
						->setMime(\lola\io\http\HttpConfig::MIME_HTML)
						->sendOB();
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
}
