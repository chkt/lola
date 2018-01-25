<?php

namespace test\error;

use PHPUnit\Framework\TestCase;

use eve\common\IDriver;
use eve\common\access\TraversableAccessor;
use eve\inject\IInjector;
use eve\inject\IInjectable;
use eve\inject\IInjectableIdentity;
use lola\error\IErrorHandler;
use lola\error\IErrorEmitter;
use lola\error\IErrorDriver;
use lola\error\ErrorDriver;



final class ErrorDriverTest
extends TestCase
{

	private function _mockHandler() {
		$ins = $this
			->getMockBuilder(IErrorHandler::class)
			->getMock();

		return $ins;
	}

	private function _mockEmitter() {
		$ins = $this
			->getMockBuilder(IErrorEmitter::class)
			->getMock();

		return $ins;
	}


	private function _mockInjector(IErrorEmitter $emitter = null) {
		if (is_null($emitter)) $emitter = $this->_mockEmitter();

		$ins = $this
			->getMockBuilder(IInjector::class)
			->getMock();

		$ins
			->expects($this->once())
			->method('produce')
			->with($this->isType('string'))
			->willReturn($emitter);

		return $ins;
	}


	private function _produceDriver(IInjector $injector = null) {
		if (is_null($injector)) $injector = $this->_mockInjector();

		return new ErrorDriver($injector);
	}

	private function _produceAccessor(array $data = []) : TraversableAccessor {
		return new TraversableAccessor($data);
	}


	public function testInheritance() {
		$driver = $this->_produceDriver();

		$this->assertInstanceOf(IErrorDriver::class, $driver);
		$this->assertInstanceOf(IInjectableIdentity::class, $driver);
		$this->assertInstanceOf(IInjectable::class, $driver);
		$this->assertInstanceOf(IDriver::class, $driver);
	}

	public function testDependencyConfig() {
		$this->assertEquals([
			'injector:'
		], ErrorDriver::getDependencyConfig($this->_produceAccessor()));
	}

	public function testInstanceIdentity() {
		$this->assertEquals(IInjectableIdentity::IDENTITY_DEFAULT, ErrorDriver::getInstanceIdentity($this->_produceAccessor()));
		$this->assertEquals('foo', ErrorDriver::getInstanceIdentity($this->_produceAccessor([ 'id' => 'foo'])));
	}


	public function testHasHandler() {
		$handler = $this->_mockHandler();
		$emitter = $this->_mockEmitter();
		$injector = $this->_mockInjector($emitter);
		$driver = $this->_produceDriver($injector);

		$emitter
			->expects($this->once())
			->method('appendItem')
			->with($this->equalTo($handler))
			->willReturnSelf();

		$emitter
			->expects($this->once())
			->method('removeIndex')
			->with($this->equalTo(0))
			->willReturnSelf();

		$emitter
			->expects($this->once())
			->method('indexOfItem')
			->with($this->equalTo($handler))
			->willReturn(0);

		$this->assertFalse($driver->hasHandler($handler));
		$this->assertSame($driver, $driver->setHandler($handler));
		$this->assertTrue($driver->hasHandler($handler));
		$this->assertSame($driver, $driver->removeHandler($handler));
		$this->assertFalse($driver->hasHandler($handler));
	}

	public function testRemoveHandler() {
		$handler = $this->_mockHandler();
		$emitter = $this->_mockEmitter();
		$injector = $this->_mockInjector($emitter);
		$driver = $this->_produceDriver($injector);

		$emitter
			->expects($this->once())
			->method('removeIndex')
			->with($this->equalTo(0))
			->willReturnSelf();

		$emitter
			->expects($this->once())
			->method('indexOfItem')
			->with($this->equalTo($handler))
			->willReturn(0);

		$emitter
			->expects($this->once())
			->method('appendItem')
			->with($this->equalTo($handler))
			->willReturnSelf();

		$this->assertSame($driver, $driver->setHandler($handler));
		$this->assertSame($driver, $driver->removeHandler($handler));
		$this->assertSame($driver, $driver->removeHandler($handler));
	}

	public function testSetHandler() {
		$handler = $this->_mockHandler();
		$emitter = $this->_mockEmitter();
		$injector = $this->_mockInjector($emitter);
		$driver = $this->_produceDriver($injector);

		$emitter
			->expects($this->once())
			->method('appendItem')
			->with($this->equalTo($handler))
			->willReturnSelf();

		$this->assertSame($driver, $driver->setHandler($handler));
		$this->assertSame($driver, $driver->setHandler($handler));
	}
}
