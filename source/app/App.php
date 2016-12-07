<?php

namespace lola\app;

use \lola\app\TAppBase;
use \lola\app\TAppFile;
use \lola\inject\TAppInjector;
use \lola\prov\TAppLocator;
use \lola\module\TAppRegistry;



class App
implements IApp
{
	use TAppBase;
	use TAppFile;
	use TAppInjector;
	use TAppLocator;
	use TAppRegistry;



	const VERSION = '0.5.2';



	protected $_dict = [];


	public function __construct(array $config) {
		$this->_dict = $config;

		date_default_timezone_set(array_key_exists('timezone', $config) ? $config['timezone'] : 'UTC');

		ob_start();
	}


	/**
	 * Returns true if a property named $name exists, false otherwise
	 * @param string $name
	 * @return bool
	 * @throws \ErrorException if $name is empty
	 */
	public function hasProperty(string $name) : bool {
		if (empty($name)) throw new \ErrorException();

		return array_key_exists($name, $this->_dict);

	}

	/**
	 * Returns the value of property $name
	 * @param string $name
	 * @return mixed
	 * @throws \ErrorException if $name is empty
	 * @throws \ErrorException if $name does not exist
	 */
	public function getProperty(string $name) {
		if (empty($name) || !array_key_exists($name, $this->_dict)) throw new \ErrorException();

		return $this->_dict[$name];
	}
}
