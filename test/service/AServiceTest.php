<?php

namespace test\service;


use PHPUnit\Framework\TestCase;

use eve\common\access\TraversableAccessor;
use eve\inject\IInjectable;
use eve\inject\IInjectableIdentity;
use lola\service\IService;
use lola\service\AService;



final class AServiceTest
extends TestCase
{

	private function _mockService() {
		$service = $this
			->getMockBuilder(AService::class)
			->getMockForAbstractClass();

		return $service;
	}


	private function _produceAccessor(array& $data = []) : TraversableAccessor {
		return new TraversableAccessor($data);
	}


	public function testInheritance() {
		$service = $this->_mockService();

		$this->assertInstanceOf(IService::class, $service);
		$this->assertInstanceOf(IInjectableIdentity::class, $service);
		$this->assertInstanceOf(IInjectable::class, $service);
	}

	public function testDependencyConfig() {
		$this->assertEquals([], AService::getDependencyConfig($this->_produceAccessor()));
	}

	public function testInstanceIdentity() {
		$this->assertEquals(IInjectableIdentity::IDENTITY_SINGLE, AService::getInstanceIdentity($this->_produceAccessor()));
	}
}
