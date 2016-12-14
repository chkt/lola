<?php

namespace test\service;

use PHPUnit\Framework\TestCase;

use lola\inject\IInjectable;
use lola\module\Registry;
use lola\service\ServiceProvider;


final class ServiceProviderTest
extends TestCase
{

	public function testGetDependencyConfig() {
		$registry = $this
			->getMockBuilder(Registry::class)
			->disableOriginalConstructor()
			->getMock();


		$this->assertEquals([
			'environment:registry'
		], ServiceProvider::getDependencyConfig([]));

		$this->assertInstanceOf(IInjectable::class, new ServiceProvider($registry));
	}
}
