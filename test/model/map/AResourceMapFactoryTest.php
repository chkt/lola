<?php

namespace test\model\map;

use PHPUnit\Framework\TestCase;

use eve\access\TraversableAccessor;
use eve\inject\IInjector;
use lola\common\factory\AStatelessInjectorFactory;
use lola\model\map\IResourceMap;
use lola\model\map\AResourceMapFactory;



final class AResourceMapFactoryTest
extends TestCase
{

	private function _mockResource() {
		return $this
			->getMockBuilder(IResourceMap::class)
			->getMock();
	}

	private function _mockInjector() {
		$injector = $this
			->getMockBuilder(IInjector::class)
			->getMock();

		$injector
			->expects($this->any())
			->method('produce')
			->with($this->equalTo(IResourceMap::class))
			->willReturn($this->_mockResource());

		return $injector;
	}

	private function _mockFactory() {
		$injector = $this->_mockInjector();

		return $this
			->getMockBuilder(AResourceMapFactory::class)
			->setConstructorArgs([$injector, IResourceMap::class])
			->getMockForAbstractClass();
	}


	private function _produceAccessor(array& $data = []) {
		return new TraversableAccessor($data);
	}


	public function testInheritance() {
		$factory = $this->_mockFactory();

		$this->assertInstanceOf(AStatelessInjectorFactory::class, $factory);
	}

	public function testDependencyConfig() {
		$this->assertEquals([ 'injector:' ], AResourceMapFactory::getDependencyConfig($this->_produceAccessor()));
	}


	public function test_produceInstance_read() {
		$factory = $this->_mockFactory();

		$this->assertInstanceOf(IResourceMap::class, $factory->produce($this->_produceAccessor()));
	}

	public function test_produceInstance_pass() {
		$resource = $this->_mockResource();

		$data = [
			'mode' => AResourceMapFactory::MODE_PASS,
			'resource' => $resource
		];

		$factory = $this->_mockFactory();

		$this->assertEquals($resource, $factory->produce($this->_produceAccessor($data)));
	}

	public function test_produceInstance_other() {
		$data = [
			'mode' => AResourceMapFactory::MODE_NONE
		];

		$factory = $this->_mockFactory();

		$this->expectException(\ErrorException::class);

		$factory->produce($this->_produceAccessor($data));
	}
}
