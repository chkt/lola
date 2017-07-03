<?php

namespace test\prov;

use PHPUnit\Framework\TestCase;

use lola\app\App;
use lola\inject\IInjectable;
use lola\prov\EnvironmentProvider;
use lola\module\Registry;
use lola\io\http\IHttpDriver;
use lola\log\ILogger;



final class EnvironmentProviderTest
extends TestCase
{

	public function testUsing() {
		$app = new App();

		$this->assertEquals([ 'resolve:app' ], EnvironmentProvider::getDependencyConfig([]));

		$provider = new EnvironmentProvider($app);

		$this->assertInstanceOf(IInjectable::class, $provider);
		$this->assertInstanceOf(IHttpDriver::class, $provider->using('http'));
		$this->assertInstanceOf(ILogger::class, $provider->using('log'));
		$this->assertInstanceOf(Registry::class, $provider->using('registry'));
	}
}
