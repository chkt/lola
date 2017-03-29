<?php

namespace lola\app;

use \lola\app\TAppBase;
use \lola\app\TAppFile;

use lola\inject\IInjector;
use lola\inject\Injector;
use lola\prov\ProviderProvider;



class App
implements IApp
{
	use TAppBase;
	use TAppFile;



	const VERSION = '0.5.2';



	private $_injector;
	private $_locator;

	protected $_dict;


	public function __construct(array $config = []) {
		$this->_dict = $config;

		date_default_timezone_set(array_key_exists('timezone', $config) ? $config['timezone'] : 'UTC');

		ob_start();
	}


	/**
	 * Return a reference to the injector associated with the instance
	 * @returns Injector
	 */
	public function& useInjector() : IInjector {
		if (is_null($this->_injector)) $this->_injector = new Injector($this->useLocator(), [
			'app' => & $this
		]);

		return $this->_injector;
	}

	/**
	 * Returns a reference to the locator associated with the instance
	 * @return ProviderProvider
	 */
	public function& useLocator() : ProviderProvider {
		if (is_null($this->_locator)) $this->_locator = new ProviderProvider($this);

		return $this->_locator;
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
