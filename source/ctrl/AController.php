<?php

namespace lola\ctrl;

use lola\inject\IInjectable;

use lola\route\Route;
use lola\route\RouteCanceledException;



abstract class AController
implements IInjectable
{
	
	/**
	 * The version string
	 */
	const VERSION = '0.3.3';
	
	
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
	 * Reenters the instance through $action
	 * @param string $action The controller action
	 * @param Route $route The associated route
	 * @return mixed
	 * @throws \ErrorException if $action is not a nonempty string
	 * @throws \ErrorException if $action does not reference an action of the instance
	 */
	protected function _reenter($action, Route& $route) {
		if (!is_string($action) || empty($action)) throw new \ErrorException();
		
		$method = $action . 'Action';
		
		if (!method_exists($this, $method)) throw new \ErrorException();
		
		$route->setAction($action);
		
		return $this->$method($route);
	}
	
	
	/**
	 * Enters the action of the instance referenced by $route
	 * @param Route $route The associated route
	 * @return mixed
	 */
	public function enter(Route& $route) {		
		$method = $route->getAction() . 'Action';
		
		if (!method_exists($this, $method)) $method = 'defaultAction';
		
		return $this->$method($route);
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
