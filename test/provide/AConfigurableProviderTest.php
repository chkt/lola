<?php

namespace test\prov;

use PHPUnit\Framework\TestCase;

use eve\common\factory\ICoreFactory;
use eve\common\factory\ISimpleFactory;
use eve\common\access\ITraversableAccessor;
use eve\common\access\IItemMutator;
use eve\common\access\TraversableAccessor;
use eve\common\assembly\IAssemblyHost;
use eve\inject\IInjector;
use eve\inject\cache\IKeyEncoder;
use eve\provide\AProvider;
use lola\provide\IConfigurableProvider;
use lola\provide\AConfigurableProvider;



final class AConfigurableProviderTest
extends TestCase
{

	private function _mockInterface(string $qname) {
		$ins = $this
			->getMockBuilder($qname)
			->getMock();

		return $ins;
	}

	private function _mockInjector($ins = null) {
		$injector = $this->_mockInterface(IInjector::class);

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
		$factory = $this->_mockInterface(ICoreFactory::class);

		return $factory;
	}

	private function _mockAccessorFactory() {
		$item = $this->_mockInterface(ITraversableAccessor::class);
		$access = $this->_mockInterface(ISimpleFactory::class);

		$access
			->method('produce')
			->willReturn($item);

		return $access;
	}

	private function _mockEncoder() {
		$encoder = $this->_mockInterface(IKeyEncoder::class);

		$encoder
			->method('encodeIdentity')
			->with($this->isType('string'), $this->isInstanceOf(ITraversableAccessor::class))
			->willReturnCallback(function(string $qname, ITraversableAccessor $config) {
				return 'bar';
			});

		return $encoder;
	}

	private function _mockCache() {
		$cache = $this->_mockInterface(IItemMutator::class);

		return $cache;
	}

	private function _mockDriverAssembly(
		IInjector $injector = null,
		ICoreFactory $base = null,
		IItemMutator $cache = null,
		IKeyEncoder $encoder = null,
		ISimpleFactory $accessorFactory = null
	) {
		if (is_null($injector)) $injector = $this->_mockInjector();
		if (is_null($base)) $base = $this->_mockFactory();
		if (is_null($encoder)) $encoder = $this->_mockEncoder();
		if (is_null($cache))  $cache = $this->_mockCache();
		if (is_null($accessorFactory)) $accessorFactory = $this->_mockAccessorFactory();

		$driver = $this->_mockInterface(IAssemblyHost::class);

		$driver
			->method('getItem')
			->with($this->isType('string'))
			->willReturnCallback(function(string $key) use ($injector, $base, $encoder, $cache, $accessorFactory) {
				if ($key === 'injector') return $injector;
				else if ($key === 'coreFactory') return $base;
				else if ($key === 'accessorFactory') return $accessorFactory;
				else if ($key === 'keyEncoder') return $encoder;
				else if ($key === 'instanceCache') return $cache;
				else $this->fail($key);
			});

		return $driver;
	}


	private function _mockProvider(IInjector $injector = null, ICoreFactory $base = null, IItemMutator $cache = null) {
		$driver = $this->_mockDriverAssembly($injector, $base, $cache);

		$prov = $this
			->getMockBuilder(AConfigurableProvider::class)
			->setConstructorArgs([ $driver ])
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
		$driver = $this->_mockDriverAssembly();
		$data = [ 'driver' => $driver ];

		$this->assertEquals([[
			'type' => IInjector::TYPE_ARGUMENT,
			'data' => $driver
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
