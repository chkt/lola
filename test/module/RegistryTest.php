<?php

namespace test\module;

use PHPUnit\Framework\TestCase;

use lola\app\IApp;
use lola\module\Registry;
use lola\inject\IInjectable;



final class RegistryTest
extends TestCase
{

	public function testGetDependencyConfig() {
		$app = $this
			->getMockBuilder(IApp::class)
			->getMock();

		$this->assertInstanceOf(IInjectable::class, new Registry($app));
		$this->assertEquals([ 'resolve:app' ], Registry::getDependencyConfig([]));
	}
}
