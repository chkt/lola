<?php

namespace test\prov;

use PHPUnit\Framework\TestCase;

use eve\common\factory\ICoreFactory;
use eve\common\access\IItemMutator;
use eve\common\access\TraversableAccessor;
use eve\driver\IInjectorDriver;
use eve\inject\IInjector;
use eve\inject\IInjectableIdentity;
use eve\provide\AProvider;
use lola\provide\IConfigurableProvider;
use lola\provide\AConfigurableProvider;



final class AConfigurableProviderTest
extends TestCase
{

	private function _mockInjector($ins = null) {
		$injector = $this
			->getMockBuilder(IInjector::class)
			->getMock();

		$injector
			->expects($this->any())
			->method('produce')
			->with($this->equalTo('foo'))
			->willReturnCallback(function(string $qname) use ($ins) {
				$ins->injected = true;

				return $ins;
			});

		return $injector;
	}

	private function _mockFactory() {
		$factory = $this
			->getMockBuilder(ICoreFactory::class)
			->getMock();

		$factory
			->expects($this->any())
			->method('hasInterface')
			->with(
				$this->equalTo('foo'),
				$this->equalTo(IInjectableIdentity::class)
			)
			->willReturn(true);

		$factory
			->expects($this->any())
			->method('callMethod')
			->with(
				$this->equalTo('foo'),
				$this->equalTo('getInstanceIdentity'),
				$this->equalTo([])
			)
			->willReturn('bar');

		return $factory;
	}

	private function _mockCache() {
		$cache = $this
			->getMockBuilder(IItemMutator::class)
			->getMock();

		return $cache;
	}

	private function _mockDriver(IInjector $injector = null, ICoreFactory $factory = null, IItemMutator $cache = null) {
		if (is_null($injector)) $injector = $this->_mockInjector();
		if (is_null($factory)) $factory = $this->_mockFactory();
		if (is_null($cache))  $cache = $this->_mockCache();

		$driver = $this
			->getMockBuilder(IInjectorDriver::class)
			->getMock();

		$driver
			->expects($this->once())
			->method('getInjector')
			->with()
			->willReturn($injector);

		$driver
			->expects($this->once())
			->method('getCoreFactory')
			->with()
			->willReturn($factory);

		$driver
			->expects($this->once())
			->method('getInstanceCache')
			->with()
			->willReturn($cache);

		return $driver;
	}


	private function _mockProvider(IInjector $injector = null, ICoreFactory $factory = null, IItemMutator $cache = null) {
		if (is_null($injector)) $injector = $this->_mockInjector();
		if (is_null($factory)) $factory = $this->_mockFactory();
		if (is_null($cache))  $cache = $this->_mockCache();

		$prov = $this
			->getMockBuilder(AConfigurableProvider::class)
			->setConstructorArgs([ $injector, $factory, $cache ])
			->getMockForAbstractClass();

		$prov
			->expects($this->any())
			->method('_parseEntity')
			->with($this->equalTo('foo'))
			->willReturn([
				'qname' => 'foo',
				'config' => []
			]);

		return $prov;
	}


	private function _produceAccessor(array& $data = []) : TraversableAccessor {
		return new TraversableAccessor($data);
	}


	public function testInheritance() {
		$prov = $this->_mockProvider();

		$this->assertInstanceOf(AProvider::class, $prov);
		$this->assertInstanceOf(IConfigurableProvider::class, $prov);
	}

	public function testDependencyConfig() {
		$injector = $this->_mockInjector();
		$factory = $this->_mockFactory();
		$cache = $this->_mockCache();
		$data = [
			'driver' => $this->_mockDriver($injector, $factory, $cache)
		];

		$this->assertEquals([[
			'type' => IInjector::TYPE_ARGUMENT,
			'data' => $injector
		], [
			'type' => IInjector::TYPE_ARGUMENT,
			'data' => $factory
		], [
			'type' => IInjector::TYPE_ARGUMENT,
			'data' => $cache
		]], AConfigurableProvider::getDependencyConfig($this->_produceAccessor($data)));
	}


	public function testAddConfiguration() {
		$count = 0;
		$ins = new \stdClass();

		$injector = $this->_mockInjector($ins);
		$cache = $this->_mockCache();

		$cache
			->expects($this->at(0))
			->method('hasKey')
			->with('bar')
			->willReturn(false);

		$cache
			->expects($this->at(1))
			->method('hasKey')
			->with('bar')
			->willReturn(false);

		$cache
			->expects($this->at(2))
			->method('hasKey')
			->with('bar')
			->willReturn(true);

		$cache
			->expects($this->at(3))
			->method('getItem')
			->with('bar')
			->willReturn($ins);

		$prov = $this->_mockProvider($injector, null, $cache);

		$this->assertSame($prov, $prov->addConfiguration('foo', function($ins) use (& $count) {
			$ins->first = ++$count;
		}));
		$this->assertSame($prov, $prov->addConfiguration('foo', function($ins) use (& $count) {
			$ins->second = ++$count;
		}));

		$count += 1;

		$this->assertSame($prov, $prov->addConfiguration('foo', function($ins) use (& $count) {
			$ins->third = ++$count;
		}));

		$this->assertEquals(2, $ins->first);
		$this->assertEquals(3, $ins->second);
		$this->assertEquals(4, $ins->third);
	}

	public function testGetItem() {
		$ins = new \stdClass();
		$ins->count = 0;
		$ins->injected = false;

		$injector = $this->_mockInjector($ins);
		$cache = $this->_mockCache();

		$cache
			->expects($this->exactly(3))
			->method('hasKey')
			->with($this->equalTo('bar'))
			->willReturnCallback(function(string $id) use ($ins) : bool {
				return $ins->injected;
			});

		$cache
			->expects($this->exactly(2))
			->method('getItem')
			->with($this->equalTo('bar'))
			->willReturnCallback(function(string $id) use ($ins) {
				$this->assertTrue($ins->injected);

				return $ins;
			});

		$fn = function($ins) {
			$ins->count += 1;
		};

		$prov = $this->_mockProvider($injector, null, $cache);

		$this->assertSame($prov, $prov->addConfiguration('foo', $fn));
		$this->assertSame($prov, $prov->addConfiguration('foo', $fn));
		$this->assertEquals(0, $ins->count);

		$this->assertSame($ins, $prov->getItem('foo'));
		$this->assertEquals(2, $ins->count);

		$this->assertSame($prov, $prov->addConfiguration('foo', $fn));
		$this->assertEquals(3, $ins->count);

		$this->assertSame($ins, $prov->getItem('foo'));
		$this->assertEquals(3, $ins->count);
	}
}
