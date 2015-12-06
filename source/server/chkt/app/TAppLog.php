<?php

namespace chkt\app;

use chkt\prov\LoggerProvider;



trait TAppLog {
	
	protected $_dict = [];
	
	protected $_tLogProv = null;
	
	
	public function getLoggerProvider() {
		if (is_null($this->_tLogProv)) $this->_tLogProv = new LoggerProvider($this);
		
		return $this->_tLogProv;
	}
}
