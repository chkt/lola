<?php

namespace test\app;

use PHPUnit\Framework\TestCase;

use eve\common\IHost;
use eve\access\IKeyAccessor;
use eve\access\IItemAccessor;
use eve\access\ItemAccessor;
use eve\access\TraversableAccessor;
use eve\driver\IInjectorHost;
use eve\driver\IInjectorDriver;
use eve\inject\IInjector;
use eve\inject\IInjectable;
use eve\provide\ILocator;
use lola\common\IComponentConfig;
use lola\app\IApp;
use lola\app\App;



final class AppTest
extends TestCase
{

	private function _mockInjector() {
		$ins = $this
			->getMockBuilder(IInjector::class)
			->getMock();

		return $ins;
	}

	private function _mockLocator() {
		$ins = $this
			->getMockBuilder(ILocator::class)
			->getMock();

		return $ins;
	}

	private function _mockDriver(IInjector $injector = null, ILocator $locator = null) {
		if (is_null($injector)) $injector = $this->_mockInjector();
		if (is_null($locator)) $locator = $this->_mockLocator();

		$ins = $this
			->getMockBuilder(IInjectorDriver::class)
			->getMock();

		$ins
			->expects($this->any())
			->method('getInjector')
			->with()
			->willReturn($injector);

		$ins
			->expects($this->any())
			->method('getLocator')
			->with()
			->willReturn($locator);

		return $ins;
	}

	private function _mockConfig() {
		$ins = $this
			->getMockBuilder(IComponentConfig::class)
			->getMock();

		return $ins;
	}


	private function _produceAccessor(array $data) : TraversableAccessor {
		return new TraversableAccessor($data);
	}

	private function _produceApp(IInjectorDriver $driver = null, IComponentConfig $config = null) {
		if (is_null($driver)) $driver = $this->_mockDriver();
		if (is_null($config)) $config = $this->_mockConfig();

		return new App($driver, $config);
	}


	public function testInheritance() {
		$app = $this->_produceApp();

		$this->assertInstanceOf(IApp::class, $app);
		$this->assertInstanceOf(IInjectorHost::class, $app);
		$this->assertInstanceOf(IHost::class, $app);
		$this->assertInstanceOf(ItemAccessor::class, $app);
		$this->assertInstanceOf(IItemAccessor::class, $app);
		$this->assertInstanceOf(IKeyAccessor::class, $app);
		$this->assertInstanceOf(IInjectable::class, $app);
	}

	public function testDependencyConfig() {
		$driver = $this->_mockDriver();
		$component = $this->_mockConfig();

		$this->assertEquals([[
			'type' => IInjector::TYPE_ARGUMENT,
			'data' => $driver
		], [
			'type' => IInjector::TYPE_ARGUMENT,
			'data' => $component
		]], App::getDependencyConfig($this->_produceAccessor([
			'driver' => $driver,
			'component' => $component
		])));
	}


	public function testGetInjector() {
		$injector = $this->_mockInjector();
		$driver = $this->_mockDriver($injector);
		$app = $this->_produceApp($driver);

		$this->assertSame($injector, $app->getInjector());
		$this->assertSame($injector, $app->getItem('injector'));
	}

	public function testGetLocator() {
		$locator = $this->_mockLocator();
		$driver = $this->_mockDriver(null, $locator);
		$app = $this->_produceApp($driver);

		$this->assertSame($locator, $app->getLocator());
		$this->assertSame($locator, $app->getItem('locator'));
	}

	public function testGetConfig() {
		$config = $this->_mockConfig();
		$app = $this->_produceApp(null, $config);

		$this->assertSame($config, $app->getConfig());
		$this->assertSame($config, $app->getItem('config'));
	}
}
