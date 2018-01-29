<?php

namespace test\app;

use PHPUnit\Framework\TestCase;

use eve\common\factory\ICoreFactory;
use eve\common\factory\ISimpleFactory;
use eve\common\factory\ASimpleFactory;
use eve\common\access\TraversableMutator;
use eve\driver\IInjectorDriver;
use eve\inject\IInjectableIdentity;
use eve\inject\IInjector;
use lola\common\IComponentConfig;
use lola\error\INativeErrorSource;
use lola\error\NativeErrorSource;
use lola\app\IApp;
use lola\app\CoreProvider;
use lola\app\InjectorDriverFactory;
use lola\app\AppFactory;
use lola\app\AppConfig;
use lola\app\App;



final class AppFactoryTest
extends TestCase
{

	private function _mockInterface(string $iname, array $args = []) {
		$ins = $this
			->getMockBuilder($iname)
			->getMock();

		foreach ($args as $key => $value) {
			$key = (is_numeric($key) ? 'p' : '') . $key;

			$ins->$key = $value;
		}

		return $ins;
	}


	private function _mockDriverFactory() {
		$ins = $this
			->getMockBuilder(ISimpleFactory::class)
			->setMethods([ 'produce', 'useReferenceSource' ])
			->getMock();

		$ins
			->expects($this->once())
			->method('produce')
			->with($this->isType('array'))
			->willReturnCallback(function(array $config) {
				return $this->_mockDriver();
			});

		$ins
			->expects($this->once())
			->method('useReferenceSource')
			->with()
			->willReturn([]);

		return $ins;
	}

	private function _mockDriver() {
		$cache = $this->_produceCache();

		$ins = $this->_mockInterface(IInjectorDriver::class);

		$ins
			->expects($this->once())
			->method('getInjector')
			->with()
			->willReturnCallback(function() {
				return $this->_mockInjector();
			});

		$ins
			->method('getInstanceCache')
			->willReturn($cache);

		return $ins;
	}

	private function _mockInjector() {
		$ins = $this->_mockInterface(IInjector::class);

		$ins
			->expects($this->exactly(2))
			->method('produce')
			->with(
				$this->isType('string'),
				$this->isType('array')
			)
			->willReturnCallback(function(string $qname, array $args) {
				if ($qname === App::class) return $this->_mockInterface(IApp::class, $args);
				else if ($qname === NativeErrorSource::class) return $this->_mockInterface(INativeErrorSource::class);
			});

		return $ins;
	}

	private function _produceCache() {
		$data = [];

		return new TraversableMutator($data);
	}


	private function _produceAppFactory(ICoreFactory $core = null) : AppFactory {
		if (is_null($core)) $core = $this->_mockInterface(ICoreFactory::class);

		return new AppFactory($core);
	}


	public function testInheritance() {
		$appFactory = $this->_produceAppFactory();

		$this->assertInstanceOf(ASimpleFactory::class, $appFactory);
	}

	public function testProduce() {
		$core = $this->_mockInterface(ICoreFactory::class);

		$core
			->expects($this->exactly(2))
			->method('newInstance')
			->with(
				$this->isType('string'),
				$this->logicalOr(
					$this->isType('array'),
					$this->isNull()
				)
			)
			->willReturnCallback(function(string $qname, array $args) {
				if ($qname === InjectorDriverFactory::class) return $this->_mockDriverFactory();
				else if ($qname === AppConfig::class) return $this->_mockInterface(IComponentConfig::class);
			});

		$config = [];
		$app = $this
			->_produceAppFactory($core)
			->produce($config);

		$this->assertInstanceOf(IApp::class, $app);
		$this->assertInstanceOf(IInjectorDriver::class, $app->driver);
		$this->assertSame($app->driver, $app->driver->getInstanceCache()->getItem(CoreProvider::class . ':' . IInjectableIdentity::IDENTITY_SINGLE));
		$this->assertInstanceOf(IComponentConfig::class, $app->component);
	}
}
