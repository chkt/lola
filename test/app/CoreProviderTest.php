<?php

namespace test\app;

use PHPUnit\Framework\TestCase;

use eve\common\access\TraversableAccessor;
use eve\common\assembly\IAssemblyHost;
use eve\driver\InjectorDriver;
use eve\inject\IInjectableIdentity;
use eve\provide\IProvider;
use lola\app\CoreProvider;



final class CoreProviderTest
extends TestCase
{

	private function _mockInterface(string $qname) {
		return $this
			->getMockBuilder($qname)
			->getMock();
	}

	private function _produceCoreProvider(IAssemblyHost $assembly = null) {
		if (is_null($assembly)) $assembly = $this->_mockInterface(IAssemblyHost::class);

		return new CoreProvider($assembly);
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
