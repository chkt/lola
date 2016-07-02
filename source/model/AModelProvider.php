<?php

namespace lola\model;

use lola\prov\AProvider;
use lola\prov\ProviderProvider;



abstract class AModelProvider
extends AProvider
{
	
	const VERSION = '0.2.4';
	
	

	private $_locator = null;
	
	public function __construct(
		ProviderProvider& $locator,
		array $config
	) {
		$this->_locator =& $locator;
		
		parent::__construct(function($id) use ($config) {
			if (!array_key_exists($id, $config)) throw new \ErrorException();
		
			$item = $config[$id];

			if (
				!array_key_exists('service', $item) ||
				!array_key_exists('query', $item) || !is_array($item['query'])
			) throw new \ErrorException();

			return $this->_locator
				->using('service')
				->using($item['service'])
				->getModel($item['query']);
		});
	}
}
