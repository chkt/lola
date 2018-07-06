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


	/**
	 * Returns a camelCase representation of <code>$path</code>
	 * @param string $path The path
	 * @return string
	 * @throws \ErrorException if <code>$path</code> is not a <code>String</code>
	 */
	static public function pathToCamel($path) {		//TODO:remove
		if (!is_string($path)) throw new \ErrorException();

		$segs = array_map(function($item) {
			return ucfirst($item);
		}, explode('/', $path));

		return lcfirst(implode('', $segs));
	}

	/**
	 * Returns a snake_case representation of <code>$path</code>
	 * @param string $path The path
	 * @return string
	 * @throws \ErrorException if <code>$path</code> is not a <code>String</code>
	 */
	static public function pathToSnake($path) {		//TODO:remove
		if (!is_string($path)) throw new \ErrorException();

		return strtolower(str_replace('/', '_', $path));
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
			if (!is_array($ret)) $ret = [ $action => $ret];

			$route->setVars($ret);
		}

		return $this;
	}
}
