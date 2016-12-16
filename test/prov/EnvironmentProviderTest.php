<?php

namespace test\prov;

use PHPUnit\Framework\TestCase;

use test\io\http\MockDriver;
use lola\io\http\HttpDriver;
use lola\log\FileLogger;
use lola\module\Registry;
use lola\app\App;

use lola\inject\IInjectable;
use lola\prov\EnvironmentProvider;



final class EnvironmentProviderTest
extends TestCase
{

	public function testUsing() {
		$app = new App();

		$this->assertEquals([ 'resolve:app' ], EnvironmentProvider::getDependencyConfig([]));

		$provider = new EnvironmentProvider($app);

		$this->assertInstanceOf(IInjectable::class, $provider);
		$this->assertInstanceOf(HttpDriver::class, $provider->using('http'));
		$this->assertInstanceOf(FileLogger::class, $provider->using('log'));
		$this->assertInstanceOf(Registry::class, $provider->using('registry'));

		$app = new App([
			App::PROP_ENVIRONMENT => [
				'http' => MockDriver::class
			]
		]);

		$provider = new EnvironmentProvider($app);

		$this->assertInstanceOf(MockDriver::class, $provider->using('http'));
		$this->assertInstanceOf(FileLogger::class, $provider->using('log'));
		$this->assertInstanceOf(Registry::class, $provider->using('registry'));
	}
}
