<?php

namespace test\controller;

use PHPUnit\Framework\TestCase;

use lola\inject\IInjectable;
use lola\module\Registry;
use lola\ctrl\ControllerProvider;



final class ControllerProviderTest
extends TestCase
{

	public function testGetDependencyConfig() {
		$registry = $this
			->getMockBuilder(Registry::class)
			->disableOriginalConstructor()
			->getMock();

		$provider = new ControllerProvider($registry);

		$this->assertEquals([ 'environment:registry' ], ControllerProvider::getDependencyConfig([]));
		$this->assertInstanceOf(IInjectable::class, $provider);
	}
}
