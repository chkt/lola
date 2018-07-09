<?php

namespace lola\ctrl;

use eve\common\access\ITraversableAccessor;



abstract class AController
implements IController
{

	static public function getDependencyConfig(ITraversableAccessor $config) : array {
		return [];
	}

	static public function getInstanceIdentity(ITraversableAccessor $config) : string {
		return $config->hasKey('id') ? $config->getItem('id') : self::IDENTITY_SINGLE;
	}



	private function _getMethodName($action) {
		return lcfirst($action) . 'Action';
	}


	public function hasAction(string $name) : bool {
		return method_exists($this, $this->_getMethodName($name));
	}


	public function enter(string $action, IControllerState $route) : IController {
		$method = $this->_getMethodName($action);

		if (!method_exists($this, $method)) throw new NoActionException($action);

		$ret = $this->$method($route);

		if (!is_null($ret)) {
			if (!is_array($ret)) $ret = [ $action => $ret ];

			$route->setVars($ret);
		}

		return $this;
	}
}
