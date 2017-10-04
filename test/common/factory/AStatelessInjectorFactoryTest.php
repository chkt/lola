<?php

namespace test\common\factory;


use PHPUnit\Framework\TestCase;

use eve\common\IFactory;
use eve\common\factory\IAccessorFactory;
use eve\common\factory\IProviderFactory;
use eve\access\ITraversableAccessor;
use eve\access\TraversableAccessor;
use eve\inject\IInjectable;
use eve\inject\IInjectableIdentity;
use eve\inject\IInjectableFactory;
use lola\common\factory\AStatelessInjectorFactory;



final class AStatelessInjectorFactoryTest
extends TestCase
{

	private function _mockFactory(callable $fn = null) {
		if (is_null($fn)) $fn = function(ITraversableAccessor $config) {
			return $config;
		};

		$factory =  $this
			->getMockBuilder(AStatelessInjectorFactory::class)
			->setMethods([ '_produceInstance' ])
			->getMockForAbstractClass();

		$factory
			->expects($this->any())
			->method('_produceInstance')
			->with($this->isInstanceOf(ITraversableAccessor::class))
			->willReturnCallback($fn);

		return $factory;
	}


	private function _produceAccessor(array& $data = []) : TraversableAccessor {
		return new TraversableAccessor($data);
	}


	public function testInheritance() {
		$factory = $this->_mockFactory();

		$this->assertInstanceOf(IInjectableFactory::class, $factory);
		$this->assertInstanceOf(IProviderFactory::class, $factory);
		$this->assertInstanceOf(IAccessorFactory::class, $factory);
		$this->assertInstanceOf(IFactory::class, $factory);
		$this->assertInstanceOf(IInjectableIdentity::class, $factory);
		$this->assertInstanceOf(IInjectable::class, $factory);
	}

	public function testInstanceIdentity() {
		$factory = $this->_mockFactory();

		$this->assertEquals(IInjectableIdentity::IDENTITY_SINGLE, AStatelessInjectorFactory::getInstanceIdentity($this->_produceAccessor()));
	}


	public function testSetConfig() {
		$factory = $this->_mockFactory();

		$this->assertSame($factory, $factory->setConfig($this->_produceAccessor()));
	}


	public function testGetInstance() {
		$accessor = $this->_produceAccessor();
		$factory = $this
			->_mockFactory()
			->setConfig($accessor);

		$this->assertSame($accessor, $factory->getInstance());
	}

	public function testGetInstance_noConfig() {
		$factory = $this->_mockFactory();

		$this->expectException(\ErrorException::class);

		$factory->getInstance();
	}


	public function testProduce() {
		$accessor = $this->_produceAccessor();
		$factory = $this->_mockFactory();

		$this->assertSame($accessor, $factory->produce($accessor));
	}
}
