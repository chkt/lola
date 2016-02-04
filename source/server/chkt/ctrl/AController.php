<?php

namespace chkt\ctrl;

use chkt\inject\IInjectable;

use chkt\route\Route;
use chkt\route\RouteCanceledException;



abstract class AController
implements IInjectable
{
	
	/**
	 * The version string
	 */
	const VERSION = '0.1.3';
	
	
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
	static public function pathToCamel($path) {
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
	static public function pathToSnake($path) {
		if (!is_string($path)) throw new \ErrorException();
		
		return strtolower(str_replace('/', '_', $path));
	}
	
	
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
	 * The default action of the controller
	 * @param Route $route The associated route
	 * @return mixed
	 */
	public function defaultAction(Route $route) {
		throw new RouteCanceledException();
	}
}
