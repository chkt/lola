<?php

namespace test\service;

use PHPUnit\Framework\TestCase;

use eve\common\factory\ICoreFactory;
use eve\common\factory\ISimpleFactory;
use eve\common\access\ITraversableAccessor;
use eve\common\access\TraversableAccessor;
use eve\common\access\IKeyMutator;
use eve\common\assembly\IAssemblyHost;
use eve\inject\IInjector;
use eve\inject\cache\IKeyEncoder;
use lola\module\IRegistry;
use lola\module\IEntityParser;
use lola\provide\AConfigurableProvider;
use lola\service\ServiceProvider;



final class ServiceProviderTest
extends TestCase
{

	private function _mockInterface(string $qname) {
		$ins = $this
			->getMockBuilder($qname)
			->getMock();

		return $ins;
	}

	private function _mockAccessorFactory() {
		$item = $this->_mockInterface(ITraversableAccessor::class);
		$access = $this->_mockInterface(ISimpleFactory::class);

		$access
			->method('produce')
			->willReturn($item);

		return $access;
	}

	private function _mockDriverAssembly(IInjector $injector = null, IEntityParser $parser = null) {
		if (is_null($injector)) $injector = $this->_mockInterface(IInjector::class);
		if (is_null($parser)) $parser = $this->_mockInterface(IEntityParser::class);

		$assembly = $this->_mockInterface(IAssemblyHost::class);

		$assembly
			->method('getItem')
			->with($this->isType('string'))
			->willReturnCallback(function (string $key) use ($injector, $parser) {
				if ($key === 'coreFactory') return $this->_mockInterface(ICoreFactory::class);
				else if ($key === 'accessorFactory') return $this->_mockAccessorFactory();
				else if ($key === 'keyEncoder') return $this->_mockInterface(IKeyEncoder::class);
				else if ($key === 'instanceCache') return $this->_mockInterface(IKeyMutator::class);
				else if ($key === 'injector') return $injector;
				else if ($key === 'entityParser') return $parser;
				else $this->fail($key);
			});

		return $assembly;
	}


	private function _produceProvider(IAssemblyHost $driver = null, IRegistry $registry = null) {
		if (is_null($driver)) $driver = $this->_mockDriverAssembly();
		if (is_null($registry)) $registry = $this->_mockInterface(IRegistry::class);

		return new ServiceProvider($driver, $registry);
	}

	private function _produceAccessor(array $data) {
		return new TraversableAccessor($data);
	}


	public function testInheritance() {
		$provider = $this->_produceProvider();

		$this->assertInstanceOf(AConfigurableProvider::class, $provider);
	}


	public function testDependencyConfig() {
		$driver = $this->_mockDriverAssembly();

		$this->assertEquals([
			[
				'type' => IInjector::TYPE_ARGUMENT,
				'data' => $driver
			],
			'environment:registry'
		], ServiceProvider::getDependencyConfig($this->_produceAccessor([
			'driver' => $driver
		])));
	}

	public function testGetItem() {
		$service = new \stdClass();
		$parser = $this->_mockInterface(IEntityParser::class);

		$parser
			->method('parse')
			->with($this->equalTo('foo'))
			->willReturn([
				'module' => 'bar',
				'name' => 'foo',
				'config' => [ 'foo' => 'bar' ]
			]);

		$registry = $this->_mockInterface(IRegistry::class);

		$registry
			->method('getQualifiedName')
			->with(
				$this->equalTo('service'),
				$this->equalTo('foo'),
				$this->equalTo('bar')
			)
			->willReturn('\\foo\\bar\\baz');

		$injector = $this->_mockInterface(IInjector::class);

		$injector
			->method('produce')
			->with(
				$this->equalTo('\\foo\\bar\\baz'),
				$this->equalTo([ 'foo' => 'bar' ]))
			->willReturn($service);

		$driver = $this->_mockDriverAssembly($injector, $parser);
		$provider = $this->_produceProvider($driver, $registry);

		$this->assertSame($service, $provider->getItem('foo'));
	}
}
