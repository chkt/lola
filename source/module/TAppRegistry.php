<?php

namespace lola\module;

use lola\module\Registry;



trait TAppRegistry {

	private $_tRegistry = null;


	public function& useRegistry() {
		if (is_null($this->_tRegistry)) {
			$registry = new Registry($this);
			$registry->loadModule('app');

			$this->_tRegistry = $registry;
		}

		return $this->_tRegistry;
	}
}
