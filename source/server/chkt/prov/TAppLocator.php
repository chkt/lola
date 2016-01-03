<?php

namespace chkt\prov;

use chkt\prov\ProviderProvider;



trait TAppLocator {
		
	protected $_tLocator = null;
	
	
	public function& useLocator() {
		if (is_null($this->_tLocator)) $this->_tLocator = new ProviderProvider($this);
		
		return $this->_tLocator;
	}
}
