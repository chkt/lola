<?php

namespace chkt\route;

use \chkt\route\Router;
use \chkt\ctrl\AController;



class Route {
	
	/**
	 * The version string
	 */
	const VERSION = '0.0.6';
	
	/**
	 * The router
	 * @var Router
	 */
	private $_router = null;
	/**
	 * The route path
	 * @var string
	 */
	private $_path   = '';
	/**
	 * The route parameters
	 * @var array
	 */
	private $_param  = null;
	
	/**
	 * The route controller name
	 * @var string
	 */
	private $_ctrl   = '';
	/**
	 * The route action name
	 * @var string
	 */
	private $_action = '';
	/**
	 * The route view name
	 * @var string
	 */
	private $_view   = '';
	
	/**
	 * The route identity data
	 * @var array 
	 */
	private $_data   = null;
	/**
	 * The route environment data
	 * @var array
	 */
	private $_vars   = null;
	
	
	
	/**
	 * Creates a new instance
	 * @param Router $router The router
	 * @param string $path   The route path
	 * @param array  $params The route parameters
	 * @throws \ErrorException if <code>$path</code> is not a <code>String</code>
	 */
	public function __construct(Router $router, $path = '', Array $params = []) {
		if (!is_string($path)) throw new \ErrorException();
		
		$this->_router = $router;
		$this->_path   = $path;
		$this->_param  = $params;
		
		$this->_ctrl   = '';
		$this->_action = '';
		$this->_view   = '';
		
		$this->_data   = [];
		$this->_vars   = [];
	}
	
	
	/**
	 * Gets the router of the instance
	 * @return Router
	 */
	public function getRouter() {
		return $this->_router;
	}
	
	
	/**
	 * Gets the path of the instance
	 * @return string
	 */
	public function getPath() {
		return $this->_path;
	}
	
	/**
	 * Sets a route parameter
	 * @param string $name  The parameter name
	 * @param mixed  $value The parameter value
	 * @return Route
	 * @throws \ErrorException if <code>$name</code> is not a <em>nonempty</em> <code>String</code>
	 */
	public function setParam($name, $value) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();
		
		$this->_param[$name] = $value;
		
		return $this;
	}
	
	/**
	 * Sets multiple route parameters
	 * @param array $params The parameters dictionary
	 * @return Route
	 */
	public function setParams(Array $params) {
		$this->_param = array_merge($this->_param, $params);
		
		return $this;
	}
	
	/**
	 * Gets a route parameter
	 * @param string $name The parameter name
	 * @return mixed
	 * @throws \ErrorException if <code>$name</code> is not a <em>nonempty</em> <code>String</code>
	 */
	public function getParam($name) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();
				
		return array_key_exists($name, $this->_param) ? $this->_param[$name] : '';
	}
	
	/**
	 * Gets multiple route parameters
	 * @param array $names (optional) The parameter names
	 * @return array
	 */
	public function getParams(Array $names = null) {
		if (is_null($names)) return $this->_param;
		
		$key = array_combine($names, array_fill(0, count($names), 1));
		
		return array_intersect_key($this->_param, $key);
	}
	
	
	/**
	 * Sets the route controller
	 * @param string $ctrl   The controller name
	 * @param string $action The action name
	 * @return Route
	 * @throws \ErrorException if <code>$ctrl</code> is not a <em>nonempty</em> <code>String</code>
	 * @throws \ErrorException if <code>$action</code> is not a <em>nonempty</em> <code>String</code>
	 */
	public function setCtrl($ctrl, $action) {
		if (
			!is_string($ctrl)   || empty($ctrl) ||
			!is_string($action) || empty($action) 
		) throw new \ErrorException();
		
		$this->_ctrl   = $ctrl;
		$this->_action = $action;
		
		return $this;
	}
	
	/**
	 * Sets the route controller instance
	 * @param string $ctrl The controller name
	 * @return Route
	 * @throws \ErrorException if <code>$ctrl</code> is not a <em>nonempty</em> <code>String</code>
	 */
	public function setCtrlName($ctrl) {
		if (!is_string($ctrl) || empty($ctrl)) throw new \ErrorException();
		
		$this->_ctrl = $ctrl;
		
		return $this;
	}
	
	/**
	 * Sets the route controller action
	 * @param string $action The controller action
	 * @return Route
	 * @throws \ErrorException if <code>$action</code> is not a <em>nonempty</em> <code>String</code>
	 */
	public function setCtrlAction($action) {
		if (!is_string($action) || empty($action)) throw new \ErrorException();
		
		$this->_action = $action;
		
		return $this;
	}
	
	/**
	 * Gets the route controller
	 * @return array
	 */
	public function getCtrl() {		
		return [$this->_ctrl, $this->_action];
	}
	
	
	/**
	 * Sets the route view
	 * @param string $view The view name
	 * @return Route
	 * @throws \ErrorException if <code>$view</code> is not a <em>nonempty</em> <code>String</code>
	 */
	public function setView($view) {
		if (!is_string($view)) throw new \ErrorException();
		
		$this->_view = $view;
		
		return $this;
	}
	
	/**
	 * Gets the route view
	 * @return string
	 */
	public function getView() {
		return $this->_view;
	}
	
	
	/**
	 * Sets a route identity datum
	 * @param string $name  The data name
	 * @param mixed  $value The data value
	 * @return Route
	 * @throws \ErrorException if <code>$name</code> is not a <em>nonempty</em> <code>String</code>
	 */
	public function setDatum($name, $value) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();
		
		$this->_data[$name] = $value;
		
		return $this;
	}
	
	/**
	 * Sets route identity data
	 * @param array $data The route identity data
	 * @return Route
	 */
	public function setData(Array $data) {
		$this->_data = array_merge($this->_data, $data);
		
		return $this;
	}
	
	/**
	 * Gets a route identity datum
	 * @param string $name The data name
	 * @return mixed
	 * @throws \ErrorException if <code>$name</code> is not a <em>nonempty</em> <code>String</code>
	 */
	public function getDatum($name) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();
		
		return array_key_exists($name, $this->_data) ? $this->_data[$name] : null;
	}
	
	/**
	 * Gets multiple route identity data
	 * @param array $names The data names
	 * @return array
	 */
	public function getData(Array $names = null) {
		if (is_null($names)) return $this->_data;
		
		$key = array_combine($names, array_fill(0, count($names), 1));
		
		return array_intersect_key($this->_data, $key);
	}
	
	/**
	 * Gets a reference to the route identity data array
	 * @return array
	 */
	public function &useData() {
		return $this->_data;
	}
	
	
	/**
	 * Sets an environment variable
	 * @param string $name The var name
	 * @param mixed  $var  The var value
	 * @return Route
	 * @throws \ErrorException if <code>$name</code> is a <em>nonempty</em> <code>String</code>
	 */
	public function setVar($name, $var) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();
		
		$this->_vars[$name] = $var;
		
		return $this;
	}
	
	/**
	 * Sets multiple environment variables
	 * @param array $vars The vars
	 * @return Route
	 */
	public function setVars(Array $vars) {
		$this->_vars = array_merge($this->_vars, $vars);
		
		return $this;
	}
	
	/**
	 * Gets an environment variable
	 * @param string $name The var name
	 * @return mixed
	 * @throws \ErrorException if <code>$name</code> is not a <em>nonempty</em> <code>String</code>
	 */
	public function getVar($name) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();
		
		return array_key_exists($name, $this->_vars) ? $this->_vars[$name] : null;
	}
	
	/**
	 * Gets multiple environment variables
	 * @param array $names The var names
	 * @return array
	 */
	public function getVars(Array $names = null) {
		if (is_null($names)) return $this->_vars;
		
		$key = array_combine($names, array_fill(0, count($names), 1));
		
		return array_intersect_key($this->_vars, $key);
	}
	
	/**	
	 * Gets a reference to the environment variable array
	 * @return array
	 */
	public function &useVars() {
		return $this->_vars;
	}
	
	
	/**
	 * Executes the route
	 * @param Callable|null $fn The initialization callback
	 * @return mixed
	 */
	public function enter(Callable $fn = null) {
		return AController::getAndEnter($this->_ctrl, $this->_action, $this, $fn);
	}
}