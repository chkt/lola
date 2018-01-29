<?php

namespace test\app;

use PHPUnit\Framework\TestCase;

use eve\common\access\TraversableAccessor;
use eve\driver\InjectorDriver;
use eve\inject\IInjectableIdentity;
use eve\provide\IProvider;
use lola\app\CoreProvider;



final class CoreProviderTest
extends TestCase
{

	private function _produceCoreProvider(array& $data = []) {
		return new CoreProvider($data);
	}

	private function _produceAccessor(array $config = []) {
		return new TraversableAccessor($config);
	}


	public function testInheritance() {
		$provider = $this->_produceCoreProvider();

		$this->assertInstanceOf(InjectorDriver::class, $provider);
		$this->assertInstanceOf(IInjectableIdentity::class, $provider);
		$this->assertInstanceOf(IProvider::class, $provider);
	}

	public function testDependencyConfig() {
		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('INJ cannot inject CoreProvider');

		CoreProvider::getDependencyConfig($this->_produceAccessor());
	}

	public function testInstanceIdentity() {
		$this->assertEquals(IInjectableIdentity::IDENTITY_SINGLE, CoreProvider::getInstanceIdentity($this->_produceAccessor()));
	}
}
