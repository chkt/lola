<?php

namespace chkt\ctrl;

use chkt\inject\IInjectable;

use chkt\route\Route;
use chkt\route\RouteCanceledException;



abstract class AController implements IInjectable {
	
	/**
	 * The version string
	 */
	const VERSION = '0.1.0';
	
	
	/**
	 * Gets the dependency configuration
	 * @param array $config The seed config
	 * @return array
	 */
	static public function getDependencyConfig(Array $config) {
		return [];
	}
	
	
	/**
	 * Returns a camelCase representation of <code>$path</code>
	 * @param string $path The path
	 * @return string
	 * @throws \ErrorException if <code>$path</code> is not a <code>String</code>
	 */
	static protected function _pathToCamel($path) {
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
	static protected function _pathToSnake($path) {
		if (!is_string($path)) throw new \ErrorException();
		
		return strtolower(str_replace('/', '_', $path));
	}
	
	
	
	/**
	 * The resolveAction transform
	 * @var Callable|null
	 */
	private $_resolveTransform = null;
	
	
	/**
	 * Reenters the instance through <code>$action</code>
	 * @param string $action The controller action
	 * @param Route& $route The associated route
	 * @return mixed
	 * @throws \ErrorException if <code>$action</code> is not a <em>nonempty</em> <code>String</code>
	 */
	protected function _reenter($action, Route& $route) {
		if (!is_string($action) || empty($action)) throw new \ErrorException();
		
		if (!method_exists($this, $action . 'Action')) $action = 'default';
		
		$route->setAction($action);
		
		return call_user_func([$this, $action . 'Action'], $route);
	}
	
	
	/**
	 * Sets the resolve action transform 
	 * @param Callable $transform The transform function
	 * @return AController
	 */
	public function setResolveTransform(Callable $transform) {
		$this->_resolveTransform = $transform;
		
		return $this;
	}
	
	/**
	 * Enters the action of the instance referenced by $route
	 * @param Route& $route The associated route
	 * @return mixed
	 * @throws ErrorException if <code>$action</code> is not a <em>nonempty</em> <code>String</code>
	 */
	public function enter(Route& $route) {
		$action = $route->getAction() . 'Action';
		
		if (!method_exists($this, $action)) $action = 'defaultAction';
		
		return $this->$action($route);
	}
	
	
	/**
	 * The resolve action of the controller
	 * @param Route& $route The associated route
	 * @return mixed
	 */
	public function resolveAction(Route& $route) {
		$action = 'default';
		
		if (is_callable($this->_resolveTransform)) {
			$ret = call_user_func($this->_resolveTransform, $route);
			
			if (is_string($ret)) $action = $ret;
		}
		
		return $this->_reenter($action, $route);
	}
	
	
	/**
	 * The default action of the controller
	 * @param Route $route The associated route
	 * @return mixed
	 */
	public function defaultAction(Route $route) {
		throw new RouteCanceledException();
	}
}
