<?php

namespace chkt\tmpl;



class LocalTwigExtension extends \Twig_Extension {
	
	public function __construct(\chkt\app\App $app) {
		$this->_app = $app;
	}
	
	
	public function getName() {
		return 'local';
	}
	
	public function getFilters() {
		return [
			new \Twig_SimpleFilter('res', [$this->_app, 'getClientResource'])
		];
	}
	
	public function getFunctions() {
		return [];
	}
}