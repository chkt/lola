<?php

namespace chkt\app;

use \chkt\prov\MandrillProvider;



trait TAppMandrill {
	
	protected $_dict = [];
	
	protected $_tMandrill = null;
	
	
	public function getMandrillProvider() {
		if (is_null($this->_tMandrill)) $this->_tMandrill = new MandrillProvider($this);
		
		return $this->_tMandrill;
	}
}
