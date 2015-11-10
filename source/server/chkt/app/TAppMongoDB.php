<?php

namespace chkt\app;

use \chkt\prov\MongoDBProvider;



trait TAppMongoDB {
	
	protected $_dict = [];
	
	protected $_tMongoDB = null;
	
	
	public function getMongoDBProvider() {
		if (is_null($this->_tMongoDB)) $this->_tMongoDB = new MongoDBProvider($this);
		
		return $this->_tMongoDB;
	}
}
