<?php

namespace chkt\app;

use chkt\prov\ProviderProvider;



trait TAppLocator {
	
	protected $_dict = [];
	
	protected $_tLocator = null;
	
	
	public function getLocator() {
		if (is_null($this->_tLocator)) $this->_tLocator = new ProviderProvider($this);
		
		return $this->_tLocator;
	}
}
