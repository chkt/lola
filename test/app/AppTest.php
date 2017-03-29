<?php

namespace test\app;

use PHPUnit\Framework\TestCase;

use lola\app\App;

use lola\inject\IInjector;
use lola\prov\ProviderProvider;



final class AppTest
extends TestCase
{

	public function testUseInjector() {
		$locator = $this
			->getMockBuilder(ProviderProvider::class)
			->disableOriginalConstructor()
			->getMock();

		$app = $this
			->getMockBuilder(App::class)
			->setMethods([ 'useLocator' ])
			->getMock();

		$app
			->expects($this->once())
			->method('useLocator')
			->willReturnReference($locator);

		$injector = $app->useInjector();

		$this->assertInstanceOf(IInjector::class, $injector);
		$this->assertEquals($injector, $app->useInjector());
	}

	public function testUseLocator() {
		$app = new App();

		$locator = $app->useLocator();

		$this->assertInstanceOf(ProviderProvider::class, $locator);
		$this->assertEquals($locator, $app->useLocator());
	}

	public function testHasProperty() {
		$app = new App([
			'foo' => 1,
			'bar' => 2
		]);

		$this->assertTrue($app->hasProperty('foo'));
		$this->assertTrue($app->hasProperty('bar'));
		$this->assertFalse($app->hasProperty('baz'));
	}

	public function testGetProperty() {
		$app = new App([
			'foo' => 1,
			'bar' => 2
		]);

		$this->assertEquals(1, $app->getProperty('foo'));
		$this->assertEquals(2, $app->getProperty('bar'));

		$this->expectException(\ErrorException::class);

		$app->getProperty('baz');
	}
}
