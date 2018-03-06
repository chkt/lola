<?php

namespace lola\provide;

use eve\common\access\ITraversableAccessor;
use eve\common\assembly\IAssemblyHost;
use eve\inject\IInjector;
use lola\module\IEntityParser;



class MapProvider
extends AConfigurableProvider
{

	static public function getDependencyConfig(ITraversableAccessor $config) : array {
		$res = parent::getDependencyConfig($config);

		$res[] = [
			'type' => IInjector::TYPE_ARGUMENT,
			'data' => $config->getItem('config')
		];

		return $res;
	}



	private $_parser;
	private $_map;


	public function __construct(IAssemblyHost $driver, array $map) {
		parent::__construct($driver);

		$this->_parser = $driver->getItem('entityParser');
		$this->_map = $map;
	}


	public function _parseEntity(string $entity) : array {
		$parts = $this->_parser->parse($entity, IEntityParser::COMPONENT_CONFIG);
		$key = $parts[IEntityParser::COMPONENT_NAME];

		if (!array_key_exists($key, $this->_map)) throw new \ErrorException(sprintf('PRV not providable "%s"', $key));

		return [
			'qname' => $this->_map[$key],
			'config' => $parts[IEntityParser::COMPONENT_CONFIG]
		];
	}
}
