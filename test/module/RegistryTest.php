<?php

namespace test\module;

use PHPUnit\Framework\TestCase;

use lola\app\IApp;
use lola\module\IModule;
use lola\module\Registry;
use lola\inject\IInjector;
use lola\inject\IInjectable;



final class RegistryTest
extends TestCase
{

	private function _mockModule(array $config) : IModule {
		$ins = $this
			->getMockBuilder(IModule::class)
			->getMock();

		$ins
			->expects($this->any())
			->method('getModuleConfig')
			->with()
			->willReturn($config);

		return $ins;
	}

	private function _mockInjector(callable $fn = null) : IInjector {
		if (is_null($fn)) $fn = function(string $qname, array $config = null) {
			return [
				'qname' => $qname,
				'config' => !is_null($config) ? $config : []
			];
		};

		$ins = $this
			->getMockBuilder(IInjector::class)
			->getMock();

		$ins
			->expects($this->any())
			->method('produce')
			->with($this->isType('string'), $this->anything())
			->willReturnCallback($fn);

		return $ins;
	}

	private function _mockApp(IInjector $injector = null) : IApp {
		if (is_null($injector)) $injector = $this->_mockInjector();

		$ins = $this
			->getMockBuilder(IApp::class)
			->getMock();

		$ins
			->expects($this->any())
			->method('useInjector')
			->with()
			->willReturnReference($injector);

		return $ins;
	}

	private function _produceRegistry(IApp $app = null) : Registry {
		if (is_null($app)) $app = $this->_mockApp();

		return new Registry($app);
	}


	public function testGetDependencyConfig() {
		$registry = $this->_produceRegistry();

		$this->assertInstanceOf(IInjectable::class, $registry);
		$this->assertEquals([ 'resolve:app' ], Registry::getDependencyConfig([]));
	}


	public function testInjectModule() {
		$registry = $this->_produceRegistry();

		$this->assertEquals($registry, $registry->injectModule('foo', []));
	}

	public function testInjectModule_dependencies() {
		$count = 0;
		$injector = $this->_mockInjector(function(string $qname) use (& $count) {
			$count += 1;

			switch ($qname) {
				case '\\foo\\Module' : return $this->_mockModule([]);
				case '\\bar\\Module' : return $this->_mockModule([ 'depend' => [ 'foo' ]]);
				case '\\baz\\Module' : return $this->_mockModule([ 'depend' => [ 'foo' ]]);
				case '\\quux\\Module' : return $this->_mockModule([ 'depend' => [ 'bar', 'baz' ]]);
				default : throw new \ErrorException();
			}
		});
		$app = $this->_mockApp($injector);
		$registry = $this->_produceRegistry($app);

		$this->assertEquals($registry, $registry->injectModule('bang', [ 'depend' => [ 'quux' ]]));
		$this->assertEquals(4, $count);
	}

	public function testInjectModule_errorDependencyLoop() {
		$injector = $this->_mockInjector(function(string $qname) {
			switch ($qname) {
				case '\\foo\\Module' : return $this->_mockModule([ 'depend' => [ 'baz' ]]);
				case '\\bar\\Module' : return $this->_mockModule([ 'depend' => [ 'foo' ]]);
				default : throw new \ErrorException();
			}
		});
		$app = $this->_mockApp($injector);
		$registry = $this->_produceRegistry($app);

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('MOD: circular dependency');

		$registry->injectModule('baz', [ 'depend' => [ 'bar' ]]);
	}
}
