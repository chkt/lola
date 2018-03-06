<?php

namespace lola\model;

use eve\common\access\ITraversableAccessor;
use eve\inject\IInjector;
use eve\provide\ILocator;
use eve\provide\IProvider;
use lola\service\IGetModelService;


abstract class AModelProvider
implements IProvider
{

	static public function getDependencyConfig(ITraversableAccessor $config) : array {
		return [
			'locator:',
			[
				'type' => IInjector::TYPE_ARGUMENT,
				'data' => $config
			]
		];
	}


	private $_locator;
	private $_config;


	public function __construct(ILocator $locator, ITraversableAccessor $config) {
		$this->_locator = $locator;
		$this->_config = $config;
	}


	public function hasKey(string $key) : bool {
		return $this->_config->hasKey($key);
	}

	public function getItem(string $key) : IModel {
		if (!$this->_config->hasKey($key)) throw new \ErrorException(sprintf('PRV not providable "%s"', $key));

		$props = $this->_config->getItem($key);

		if (
			!array_key_exists('service', $props) || !is_string($props['service']) ||
			!array_key_exists('query', $props) || !is_array($props['query'])
		) throw new \ErrorException(sprintf('PRV malformed config "%s"', $key));

		$service = $this->_locator
			->getItem('service')
			->getItem($props['service']);

		if (!($service instanceof IGetModelService)) throw new \ErrorException(sprintf('PRV no model service "%s"', $key));

		return $service->getModel($props['query']);
	}
}
