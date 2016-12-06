<?php

namespace test\prov;

use PHPUnit\Framework\TestCase;

use test\io\http\MockDriver;
use lola\io\http\HttpDriver;
use lola\log\FileLogger;
use lola\app\IApp;
use lola\prov\EnvironmentProvider;



final class EnvironmentProviderTest
extends TestCase
{

	public function testUsing() {
		$app = $this
			->getMockBuilder(IApp::class)
			->getMock();

		$app
			->expects($this->at(0))
			->method('hasProperty')
			->willReturn(false);

		$app
			->expects($this->at(1))
			->method('hasProperty')
			->with($this->equalTo(IApp::PROP_ENVIRONMENT))
			->willReturn(true);

		$app
			->expects($this->once())
			->method('getProperty')
			->with($this->equalTo(IApp::PROP_ENVIRONMENT))
			->willReturn([
				'http' => MockDriver::class
			]);

		$provider = new EnvironmentProvider($app);

		$this->assertInstanceOf(HttpDriver::class, $provider->using('http'));
		$this->assertInstanceOf(FileLogger::class, $provider->using('log'));

		$provider = new EnvironmentProvider($app);

		$this->assertInstanceOf(MockDriver::class, $provider->using('http'));
		$this->assertInstanceOf(FileLogger::class, $provider->using('log'));
	}
}
