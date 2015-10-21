<?php

namespace chkt\app;

use \chkt\route\Route;



trait TAppLocations {
	
	public function buildLocations(Route $route) {
		$lang = $route->getParam('lang');
		
		$res = $route->getRouter()->toJSON('page|public', $route->getParams());
		
		if (!empty($lang)) $res['current'] = &$res[$lang];
		
		$res['active']  = $route->getData();
				
		return $res;
	}
}