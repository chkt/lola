<?php

namespace lola\route;

use lola\inject\IInjectable;
use lola\prov\ProviderProvider;

use lola\type\Collection;
use lola\type\Stack;



class Route implements IInjectable {

	/**
	 * The version string
	 */
	const VERSION = '0.1.0';


	/**
	 * Gets the dependency configuration
	 * @param array $config The config seed
	 * @return array
	 */
	static public function getDependencyConfig(Array $config) {
		return [[
			'type' => 'locator'
		], [
			'type' => 'object',
			'data' => $config
		]];
	}




	/**
	 * The locator reference
	 * @var ProviderProvider
	 */
	private $_locator = null;

	/**
	 * The route parameters
	 * @var array
	 */
	private $_param  = null;
	/**
	 * The route identity data
	 * @var array
	 */
	private $_data   = null;

	/**
	 * The route controller name
	 * @var string
	 */
	private $_ctrlName   = '';
	/**
	 * The route action name
	 * @var string
	 */
	private $_actionName = '';
	/**
	 * The route view name
	 * @var string
	 */
	private $_viewName   = '';

	/**
	 * The route environment data
	 * @var array
	 */
	private $_vars   = null;

	/**
	 * The route model data
	 * @var Collection|null
	 */
	private $_models = null;
	/**
	 * The route action result data
	 * @var Stack|null
	 */
	private $_result = null;


	/**
	 * Creates a new instance
	 * @param ProviderProvider $locator
	 * @param array $config
	 */
	public function __construct(ProviderProvider& $locator, Array $config = []) {
		$ctrl = array_key_exists('ctrl', $config) ? $config['ctrl'] : '';
		$action = array_key_exists('action', $config) ? $config['action'] : '';
		$view = array_key_exists('view', $config) ? $config['view'] : '';

		if (!is_string($ctrl) || !is_string($action) || !is_string($view)) throw new \ErrorException();

		$this->_locator =& $locator;

		$this->_param = new Collection(array_key_exists('params', $config) ? $config['params'] : []);
		$this->_data = new Collection(array_key_exists('data', $config) ? $config['data'] : []);

		$this->_ctrlName = $ctrl;
		$this->_actionName = $action;
		$this->_viewName = $view;

		$this->_vars = new Collection();

		$this->_models = null;
		$this->_result = null;
	}


	/**
	 * Sets a route parameter
	 * @param string $name  The parameter name
	 * @param mixed  $value The parameter value
	 * @return Route
	 */
	public function setParam($name, $value) {
		$this->_param->setItem($name, $value);

		return $this;
	}

	/**
	 * Sets multiple route parameters
	 * @param array $params The parameters dictionary
	 * @return Route
	 */
	public function setParams(Array $params) {
		$this->_param->getItems($params);

		return $this;
	}

	/**
	 * Returns true if route parameter $name exists, false otherwise
	 * @param string $name
	 * @return bool
	 */
	public function hasParam($name) {
		return $this->_param->hasItem($name);
	}

	/**
	 * Gets a route parameter
	 * @param string $name The parameter name
	 * @return mixed
	 */
	public function getParam($name) {
		return $this->_param->getItem($name);
	}

	/**
	 * Gets multiple route parameters
	 * @param array $names (optional) The parameter names
	 * @return array
	 */
	public function getParams(Array $names = null) {
		return $this->_param->getItems($names);
	}

	/**
	 * Gets a reference to the route params
	 * @return array
	 */
	public function& useParams() {
		return $this->_param->useItems();
	}


	/**
	 * Sets a route identity datum
	 * @param string $name  The data name
	 * @param mixed  $value The data value
	 * @return Route
	 */
	public function setRouteDatum($name, $value) {
		$this->_data->setItem($name, $value);

		return $this;
	}

	/**
	 * Sets route identity data
	 * @param array $data The route identity data
	 * @return Route
	 */
	public function setRouteData(Array $data) {
		$this->_data->setItems($data);

		return $this;
	}

	/**
	 * Returns true if route identity datum $name exists, false otherwise
	 * @param string $name
	 * @return bool
	 */
	public function hasRouteDatum($name) {
		return $this->_data->hasItem($name);
	}

	/**
	 * Gets a route identity datum
	 * @param string $name The data name
	 * @return mixed
	 */
	public function getRouteDatum($name) {
		return $this->_data->getItem($name);
	}

	/**
	 * Gets multiple route identity data
	 * @param array $names The data names
	 * @return array
	 */
	public function getRouteData(Array $names = null) {
		return $this->_data->getItems($names);
	}

	/**
	 * Gets a reference to the route identity data array
	 * @return array
	 */
	public function &useRouteData() {
		return $this->_data->useItems();
	}


	/**
	 * Sets the controller name
	 * @param string $name
	 * @return Route
	 * @throws \ErrorException if $name is not a nonempty string
	 */
	public function setCtrl($name) {
		if (!is_string($name) || empty($name)) throw new \ErrorException();

		$this->_ctrlName = $name;

		return $this;
	}

	/**
	 * Gets the controller name
	 * @return string
	 */
	public function getCtrl() {
		return $this->_ctrlName;
	}


	/**
	 * Sets the route controller action
	 * @param string $action The controller action
	 * @return Route
	 * @throws \ErrorException if $action is not a nonempty string
	 */
	public function setAction($action) {
		if (!is_string($action) || empty($action)) throw new \ErrorException();

		$this->_actionName = $action;

		return $this;
	}

	/**
	 * Gets the route controller action name
	 * @return string
	 */
	public function getAction() {
		return $this->_actionName;
	}


	/**
	 * Sets the route view
	 * @param string $view The view name
	 * @return Route
	 * @throws \ErrorException if $view is not a nonempty string
	 */
	public function setView($view) {
		if (!is_string($view)) throw new \ErrorException();

		$this->_viewName = $view;

		return $this;
	}

	/**
	 * Gets the route view
	 * @return string
	 */
	public function getView() {
		return $this->_viewName;
	}


	/**
	 * Sets an environment variable
	 * @param string $name The var name
	 * @param mixed  $value The var value
	 * @return Route
	 */
	public function setVar($name, $value) {
		$this->_vars->setItem($name, $value);

		return $this;
	}

	/**
	 * Sets multiple environment variables
	 * @param array $vars The vars
	 * @return Route
	 */
	public function setVars(Array $vars) {
		$this->_vars->setItems($vars);

		return $this;
	}

	/**
	 * Gets an environment variable
	 * @param string $name The var name
	 * @return mixed
	 */
	public function getVar($name) {
		return $this->_vars->getItem($name);
	}

	/**
	 * Gets multiple environment variables
	 * @param array $names The var names
	 * @return array
	 */
	public function getVars(Array $names = null) {
		return $this->_vars->getItems($names);
	}

	/**
	 * Gets a reference to the environment variable array
	 * @return array
	 */
	public function &useVars() {
		return $this->_vars->useItems();
	}


	/**
	 * Returns a reference to the collected models of the route
	 * @return Collection
	 */
	public function& useActionData() {
		if (is_null($this->_models)) $this->_models = new Collection();

		return $this->_models;
	}


	/**
	 * Returns a reference to the stacked action results of the route
	 * @return Stack
	 */
	public function& useActionResult() {
		if (is_null($this->_result)) $this->_result = new Stack();

		return $this->_result;
	}


	/**
	 * Executes the route
	 * @return mixed
	 */
	public function enter(Callable $fn = null) {
		$ctrl = $this->_locator->locate('controller', $this->_ctrlName);

		if (!is_null($fn)) call_user_func_array($fn, [& $ctrl ]);

		$ctrl->enter($this);
	}
}
