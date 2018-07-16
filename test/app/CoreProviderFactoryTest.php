<?php

namespace test\app;

use PHPUnit\Framework\TestCase;

use eve\common\factory\IBaseFactory;
use eve\common\factory\ISimpleFactory;
use eve\common\access\ITraversableAccessor;
use eve\common\assembly\IAssemblyHost;
use eve\inject\IInjector;
use eve\driver\IInjectorDriver;
use lola\app\CoreProvider;
use lola\app\CoreProviderFactory;
use lola\app\CoreProviderAssembly;



final class CoreProviderFactoryTest
extends TestCase
{

	private function _mockInterface(string $iname, array $args = []) {
		$ins = $this
			->getMockBuilder($iname)
			->getMock();

		foreach ($args as $key => & $value) {
			$prop = (is_numeric($key) ? 'p' : '') . $key;
			$ins->$prop =& $value;
		}

		return $ins;
	}


	private function _mockAccessorFactory(array $args) {
		$access = $this->_mockInterface(ISimpleFactory::class, $args);

		$access
			->method('produce')
			->with($this->isType('array'))
			->willReturnCallback(function(array& $data) {
				return $this->_mockInterface(ITraversableAccessor::class, [ & $data ]);
			});

		return $access;
	}

	private function _mockDriverAssembly(array $args) {
		$assembly = $this->_mockInterface(IAssemblyHost::class, $args);

		$assembly
			->method('getItem')
			->with($this->equalTo('baseFactory'))
			->willReturn($assembly->p0);

		return $assembly;
	}

	private function _mockBaseFactory(callable $mergeMethod = null) {
		if (is_null($mergeMethod)) $mergeMethod = function(array $a, array $b) : array {
			return [];
		};

		$base = $this->_mockInterface(IBaseFactory::class);

		$base
			->method('callMethod')
			->with(
				$this->equalTo(\eve\common\base\ArrayOperation::class),
				$this->equalTo('merge'),
				$this->logicalAnd(
					$this->isType('array'),
					$this->countOf(2)
				)
			)
			->willReturnCallback(function(string $qname, string $method, array $args) use ($mergeMethod) {
				return $mergeMethod(...$args);
			});

		$base
			->method('produce')
			->with(
				$this->isType('string'),
				$this->logicalOr(
					$this->isType('array'),
					$this->isNull()
				)
			)
			->willReturnCallback(function(string $qname, array $args) use ($base) {
				if ($qname === CoreProviderAssembly::class) return $this->_mockDriverAssembly($args);
				else if ($qname === 'foo') return $this->_mockAccessorFactory($args);
				else if ($qname === CoreProvider::class) return $this->_mockInterface(IInjectorDriver::class, $args);
				else $this->fail($qname);
			});

		return $base;
	}

	private function _produceProviderFactory(IBaseFactory $base = null) : CoreProviderFactory {
		if (is_null($base)) $base = $this->_mockInterface(IBaseFactory::class);

		return new CoreProviderFactory($base);
	}


	public function testInheritance() {
		$factory = $this->_produceProviderFactory();

		$this->assertInstanceOf(\eve\driver\InjectorDriverFactory::class, $factory);
	}


	public function testProduce() {
		$config = [ 'foo' => 'bar' ];

		$base = $this->_mockBaseFactory(function(array $a, array $b) use ($config) {
			$this->assertEquals([
				'resolvers' => [
					IInjector::TYPE_INJECTOR => \eve\inject\resolve\HostResolver::class,
					IInjector::TYPE_LOCATOR => \eve\inject\resolve\HostResolver::class,
					IInjector::TYPE_ARGUMENT => \eve\inject\resolve\ArgumentResolver::class,
					IInjector::TYPE_FACTORY => \eve\inject\resolve\FactoryResolver::class
				],
				'providers' => [],
				'accessorFactory' => \eve\common\access\factory\TraversableAccessorFactory::class
			], $a);

			$a['accessorFactory'] = 'foo';

			$this->assertEquals($config, $b);

			return $a;
		});
		$factory = $this->_produceProviderFactory($base);

		$driver = $factory->produce($config);

		$this->assertInstanceOf(IInjectorDriver::class, $driver);
		$this->assertObjectHasAttribute('p0', $driver);
		$this->assertInstanceOf(IAssemblyHost::class, $driver->p0);
		$this->assertObjectHasAttribute('p0', $driver->p0);
		$this->assertInstanceOf(IBaseFactory::class, $driver->p0->p0);
		$this->assertObjectHasAttribute('p1', $driver->p0);
		$this->assertInstanceOf(ISimpleFactory::class, $driver->p0->p1);
		$this->assertObjectHasAttribute('p0', $driver->p0->p1);
		$this->assertInstanceOf(IBaseFactory::class, $driver->p0->p1->p0);
		$this->assertObjectHasAttribute('p2', $driver->p0);
		$this->assertInstanceOf(ITraversableAccessor::class, $driver->p0->p2);
		$this->assertObjectHasAttribute('p0', $driver->p0->p2);
		$this->assertInternalType('array', $driver->p0->p2->p0);
		$this->assertArrayHasKey('resolvers', $driver->p0->p2->p0);
		$this->assertEquals([
			'injector' => \eve\inject\resolve\HostResolver::class,
			'locator' => \eve\inject\resolve\HostResolver::class,
			'argument' => \eve\inject\resolve\ArgumentResolver::class,
			'factory' => \eve\inject\resolve\FactoryResolver::class
		], $driver->p0->p2->p0['resolvers']);
		$this->assertArrayHasKey('providers', $driver->p0->p2->p0);
		$this->assertEquals([], $driver->p0->p2->p0['providers']);
	}
}
