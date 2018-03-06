<?php

namespace test\app;

use PHPUnit\Framework\TestCase;

use eve\common\factory\ICoreFactory;
use eve\common\factory\ISimpleFactory;
use eve\common\access\ITraversableAccessor;
use eve\common\access\TraversableAccessorFactory;
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


	private function _produceProviderFactory(ICoreFactory $core = null) : CoreProviderFactory {
		if (is_null($core)) $core = $this->_mockInterface(ICoreFactory::class);

		return new CoreProviderFactory($core);
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
		$base = $this->_mockInterface(ICoreFactory::class);

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
			->with($this->equalTo('coreFactory'))
			->willReturn($base);

		$factory = $this->_produceProviderFactory($base);
		$result = $factory->produce();

		$this->assertSame($provider, $result);
	}
}
