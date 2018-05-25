<?php

namespace test\app;

use PHPUnit\Framework\TestCase;

use eve\common\factory\IBaseFactory;
use eve\common\factory\ISimpleFactory;
use eve\common\access\ITraversableAccessor;
use eve\common\access\factory\TraversableAccessorFactory;
use eve\common\assembly\IAssemblyHost;
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


	private function _produceProviderFactory(IBaseFactory $base = null) : CoreProviderFactory {
		if (is_null($base)) $base = $this->_mockInterface(IBaseFactory::class);

		return new CoreProviderFactory($base);
	}


	public function testInheritance() {
		$factory = $this->_produceProviderFactory();

		$this->assertInstanceOf(\eve\driver\InjectorDriverFactory::class, $factory);
	}


	public function testProduce() {
		$provider = $this->_mockInterface(IInjectorDriver::class);
		$accessorFactory = $this->_mockInterface(ISimpleFactory::class);

		$accessorFactory
			->method('produce')
			->with($this->isType('array'))
			->willReturnCallback(function(array& $data) {
				return $this->_mockInterface(ITraversableAccessor::class, [ & $data ]);
			});

		$assembly = $this->_mockInterface(IAssemblyHost::class);

		$defaults = null;
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
			->willReturnCallback(function(string $qname, string $method, array $args) use(& $defaults) {
				$this->assertEquals($defaults, $args[0]);
				$this->assertEquals([], $args[1]);

				return $defaults;
			});

		$base
			->method('newInstance')
			->with($this->isType('string'))
			->willReturnCallback(function(string $qname) use ($base, $accessorFactory, $assembly, $provider) {
				if ($qname === CoreProviderAssembly::class) return $assembly;
				else if ($qname === TraversableAccessorFactory::class) return $accessorFactory;
				else if ($qname === CoreProvider::class) return $provider;
				else $this->fail($qname);
			});

		$assembly
			->method('getItem')
			->with($this->equalTo('baseFactory'))
			->willReturn($base);

		$factory = $this->_produceProviderFactory($base);

		$method = new \ReflectionMethod($factory, '_getConfigDefaults');
		$method->setAccessible(true);
		$defaults = $method->invoke($factory);

		$result = $factory->produce();

		$this->assertSame($provider, $result);
	}
}
