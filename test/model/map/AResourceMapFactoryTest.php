<?php

namespace test\model\map;

use PHPUnit\Framework\TestCase;

use lola\inject\IInjector;
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
			->setConstructorArgs([& $injector, IResourceMap::class])
			->getMockForAbstractClass();
	}


	public function testSetConfig() {
		$factory = $this->_mockFactory();

		$this->assertEquals($factory, $factory->setConfig([]));
	}

	public function testProduce_read() {
		$factory = $this
			->_mockFactory()
			->setConfig([]);

		$this->assertInstanceOf(IResourceMap::class, $factory->produce());
	}

	public function testProduce_pass() {
		$resource = $this->_mockResource();

		$factory = $this
			->_mockFactory()
			->setConfig([
				'mode' => AResourceMapFactory::MODE_PASS,
				'resource' => $resource
			]);

		$this->assertEquals($resource, $factory->produce());
	}
}
