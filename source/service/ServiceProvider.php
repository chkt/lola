<?php

namespace lola\service;

use eve\common\access\ITraversableAccessor;
use eve\common\assembly\IAssemblyHost;
use lola\module\IEntityParser;
use lola\module\IRegistry;
use lola\provide\AConfigurableProvider;



final class ServiceProvider
extends AConfigurableProvider
{

	static public function getDependencyConfig(ITraversableAccessor $config) : array {
		$res = parent::getDependencyConfig($config);

		$res[] = 'environment:registry';

		return $res;
	}



	private $_registry;
	private $_parser;


	public function __construct(IAssemblyHost $driver, IRegistry $registry) {
		parent::__construct($driver);

		$this->_registry = $registry;
		$this->_parser = $driver->getItem('entityParser');
	}


	protected function _parseEntity(string $entity) : array {
		$parts = $this->_parser->parse($entity, IEntityParser::COMPONENT_CONFIG);

		$qname = $this->_registry->getQualifiedName(
			'service',
			$parts[IEntityParser::COMPONENT_NAME],
			$parts[IEntityParser::COMPONENT_MODULE]
		);

		return [
			'qname' => $qname,
			'config' => $parts[IEntityParser::COMPONENT_CONFIG]
		];
	}
}
