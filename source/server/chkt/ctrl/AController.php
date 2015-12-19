<?php

namespace chkt\ctrl;

use chkt\type\TInjectable;
use chkt\app\IApp;

use chkt\route\Route;
use chkt\route\RouteCanceledException;



abstract class AController {
	use TInjectable;
	
	
	
	/**
	 * The version string
	 */
	const VERSION = '0.0.8';
	
	
	/**
	 * The resolveAction transform
	 * @var Callable|null
	 */
	private $_resolveTransform = null;
	
	
	
	/**
	 * DEPRECATED
	 * Gets the application state object
	 * @return IApp
	 */
	static public function getApp() {
		return array_key_exists('app', self::$_tInjectableAll) ? self::$_tInjectableAll['app'] : null;
	}
	
	
	/**
	 * Returns the class referenced by <code>$id</code>
	 * @param string $id The controller id
	 * @return string
	 * @throws ErrorException of <code>$id</code> is not a <em>nonempty</em> <code>String</code>
	 * @throws ErrorException if <code>$id</code> does not reference a class
	 */
	static public function getClass($id) {
		if (!is_string($id) || empty($id)) throw new \ErrorException();
		
		$class = '\\app\\ctrl\\' . ucfirst($id) . 'Controller';
				
		if (!class_exists($class)) throw new \ErrorException();
		
		return $class;
	}
	
	/**
	 * Returns a new instance of the class referenced by <code>$id</code>
	 * @param string $id The controller id
	 * @return \Class
	 */
	static public function getInstance($id) {		
		$class = self::getClass($id);
		
		return new $class();
	}
	
	/**
	 * Returns the result of the action referenced by $route
	 * @param Route& $route The associated route
	 * @param Callable|null $fn The initialization callback
	 * @return mixed
	 */
	static public function getAndEnter(Route& $route, Callable $fn = null) {
		$ins = self::getInstance($route->getCtrlName());
		
		if (!is_null($fn)) call_user_func($fn, $ins, $route);
		
		return $ins->enter($route);
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
	 * Reenters the instance through <code>$action</code>
	 * @param string $action The controller action
	 * @param Route& $route The associated route
	 * @return mixed
	 * @throws \ErrorException if <code>$action</code> is not a <em>nonempty</em> <code>String</code>
	 */
	protected function _reenter($action, Route& $route) {
		if (!is_string($action) || empty($action)) throw new \ErrorException();
		
		if (!method_exists($this, $action . 'Action')) $action = 'default';
		
		$route->setCtrlAction($action);
		
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
		$action = $route->getCtrlAction() . 'Action';
		
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
