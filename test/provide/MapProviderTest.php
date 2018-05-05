<?php

namespace test\provide;

use PHPUnit\Framework\TestCase;

use eve\common\factory\ICoreFactory;
use eve\common\factory\ISimpleFactory;
use eve\common\access\ITraversableAccessor;
use eve\common\access\IItemMutator;
use eve\common\access\TraversableAccessor;
use eve\common\assembly\IAssemblyHost;
use eve\inject\IInjector;
use eve\inject\cache\IKeyEncoder;
use lola\module\IEntityParser;
use lola\provide\AConfigurableProvider;
use lola\provide\MapProvider;



final class MapProviderTest
extends TestCase
{

	private function _mockInterface(string $qname) {
		$ins = $this
			->getMockBuilder($qname)
			->getMock();

		return $ins;
	}

	private function _mockInjector() {
		$injector = $this->_mockInterface(IInjector::class);

		$injector
			->method('produce')
			->with($this->isType('string'), $this->isType('array'))
			->willReturnCallback(function(string $qname, array $config) {
				$res = new \stdClass();
				$res->name = $qname;
				$res->config = $config;

				return $res;
			});

		return $injector;
	}

	private function _mockAccessorFactory() {
		$access = $this->_mockInterface(ISimpleFactory::class);
		$item = $this->_mockInterface(ITraversableAccessor::class);

		$access
			->method('produce')
			->willReturn($item);

		return $access;
	}

	private function _mockParser() {
		$parser = $this->_mockInterface(IEntityParser::class);

		$parser
			->method('parse')
			->with($this->isType('string'))
			->willReturnCallback(function(string $entity) {
				return [
					IEntityParser::COMPONENT_NAME => $entity,
					IEntityParser::COMPONENT_CONFIG => [ mb_strtoupper($entity) ]
				];
			});

		return $parser;
	}

	private function _mockDriverAssembly() {
		$assembly = $this
			->getMockBuilder(IAssemblyHost::class)
			->getMock();

		$assembly
			->method('getItem')
			->with($this->isType('string'))
			->willReturnCallback(function(string $key) {
				if ($key === 'injector') return $this->_mockInjector();
				else if ($key === 'coreFactory') return $this->_mockInterface(ICoreFactory::class);
				else if ($key === 'accessorFactory') return $this->_mockAccessorFactory();
				else if ($key === 'keyEncoder') return $this->_mockInterface(IKeyEncoder::class);
				else if ($key === 'instanceCache') return $this->_mockInterface(IItemMutator::class);
				else if ($key === 'entityParser') return $this->_mockParser();
				else $this->fail($key);
			});

		return $assembly;
	}


	private function _produceProvider(array $map = []) {
		$assembly = $this->_mockDriverAssembly();
		$access = $this->_produceAccessor($map);

		return new MapProvider($assembly, $access);
	}

	private function _produceAccessor(array $data) {
		return new TraversableAccessor($data);
	}


	public function testInheritance() {
		$provider = $this->_produceProvider();

		$this->assertInstanceOf(AConfigurableProvider::class, $provider);
	}

	public function testDependencyConfig() {
		$assembly = $this->_mockDriverAssembly();
		$config = ['foo' => 'bar'];

		$this->assertEquals([[
			'type' => IInjector::TYPE_ARGUMENT,
			'data' => $assembly
		], [
			'type' => IInjector::TYPE_ARGUMENT,
			'data' => $config
		]], MapProvider::getDependencyConfig($this->_produceAccessor([
			'driver' => $assembly,
			'config' => $config
		])));
	}


	public function testGetItem() {
		$provider = $this->_produceProvider([
			'foo' => 'bar'
		]);

		$item = $provider->getItem('foo');

		$this->assertInstanceOf(\stdClass::class, $item);
		$this->assertEquals('bar', $item->name);
		$this->assertEquals([ 'FOO' ], $item->config);
	}

	public function testGetItem_invalid() {
		$provider = $this->_produceProvider([]);

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('PRV not providable "foo"');

		$provider->getItem('foo');
	}
}
