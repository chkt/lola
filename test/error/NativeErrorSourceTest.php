<?php

namespace test\error;

use PHPUnit\Framework\TestCase;
use phpmock\phpunit\PHPMock;

use eve\access\TraversableAccessor;
use eve\inject\IInjector;
use eve\inject\IInjectableIdentity;
use eve\inject\IInjectable;
use lola\error\IErrorEmitter;
use lola\error\ErrorEmitter;
use lola\error\INativeErrorSource;
use lola\error\NativeErrorSource;
use lola\error\NativeErrorException;
use lola\error\NativeShutdownException;



final class NativeErrorSourceTest
extends TestCase
{

	use PHPMock;


	private function _mockEnvironment() {
		$ex = $this
			->getFunctionMock('\lola\error', 'set_exception_handler')
			->expects($this->once())
			->with($this->isType('array'))
			->willReturnCallback(function (array $callable) {
				$this->assertInstanceOf(NativeErrorSource::class, $callable[0]);
				$this->assertEquals('handleException', $callable[1]);

				return null;
			});

		$err = $this
			->getFunctionMock('\lola\error', 'set_error_handler')
			->expects($this->once())
			->with($this->isType('array'))
			->willReturnCallback(function(array $callable) {
				$this->assertInstanceOf(NativeErrorSource::class, $callable[0]);
				$this->assertEquals('handleError', $callable[1]);

				return null;
			});

		$sd = $this
			->getFunctionMock('\lola\error', 'register_shutdown_function')
			->expects($this->once())
			->with($this->isType('array'))
			->willReturnCallback(function(array $callable) {
				$this->assertInstanceOf(NativeErrorSource::class, $callable[0]);
				$this->assertEquals('handleShutdown', $callable[1]);
			});
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
			->expects($this->any())
			->method('produce')
			->with($this->equalTo(ErrorEmitter::class))
			->willReturn($emitter);

		return $ins;
	}

	private function _mockSource(IInjector $injector = null) {
		if (is_null($injector)) $injector = $this->_mockInjector();

		$this->_mockEnvironment();

		$ins = $this
			->getMockBuilder(NativeErrorSource::class)
			->setConstructorArgs([ $injector ])
			->setMethods([ '_terminate' ])
			->getMock();

		return $ins;
	}


	private function _produceAccessor(array& $data = []) : TraversableAccessor {
		return new TraversableAccessor($data);
	}


	public function testInheritance() {
		$source = $this->_mockSource();

		$this->assertInstanceOf(INativeErrorSource::class, $source);
		$this->assertInstanceOf(IInjectableIdentity::class, $source);
		$this->assertInstanceOf(IInjectable::class, $source);
	}

	public function testDependencyConfig() {
		$this->assertEquals([
			'injector:'
		], NativeErrorSource::getDependencyConfig($this->_produceAccessor()));
	}

	public function testInstanceIdentity() {
		$this->assertEquals(IInjectableIdentity::IDENTITY_SINGLE, NativeErrorSource::getInstanceIdentity($this->_produceAccessor()));
	}


	public function testHandleException() {
		$emitter = $this->_mockEmitter();
		$injector = $this->_mockInjector($emitter);
		$source = $this->_mockSource($injector);

		$emitter
			->expects($this->once())
			->method('handleException')
			->with($this->isInstanceOf(\Exception::class))
			->willReturn(null);

		$source
			->expects($this->once())
			->method('_terminate')
			->with()
			->willReturn(null);

		$this->assertNull($source->handleException(new \Exception()));
	}

	public function testHandleError() {
		$emitter = $this->_mockEmitter();
		$injector = $this->_mockInjector($emitter);
		$source = $this->_mockSource($injector);

		$emitter
			->expects($this->exactly(2))
			->method('handleException')
			->with($this->isInstanceOf(NativeErrorException::class))
			->willReturnCallback(function(NativeErrorException $ex) {
				if ($ex->isRecoverable()) $ex->recover();
			});

		$source
			->expects($this->once())
			->method('_terminate')
			->with()
			->willReturn(null);

		$this->assertTrue($source->handleError(E_ERROR, 'foo', 'bar', 0));
		$this->assertTrue($source->handleError(E_WARNING, 'baz', 'quux', 1));
	}

	public function testHandleError_suppressed() {
		$errors = error_reporting();

		error_reporting(0);

		$emitter = $this->_mockEmitter();
		$injector = $this->_mockInjector($emitter);
		$source = $this->_mockSource($injector);

		$emitter
			->expects($this->never())
			->method('handleException');

		$source
			->expects($this->never())
			->method('_terminate');

		$this->assertTrue($source->handleError(E_ERROR, 'foo', 'bar', 0));

		error_reporting($errors);
	}

	public function testHandleShutdown() {
		$this
			->getFunctionMock('\lola\error', 'error_get_last')
			->expects($this->once())
			->with()
			->willReturn([
				'type' => E_ERROR,
				'message' => 'foo',
				'file' => 'bar',
				'line' => 0
			]);

		$emitter = $this->_mockEmitter();
		$injector = $this->_mockInjector($emitter);
		$source = $this->_mockSource($injector);

		$emitter
			->expects($this->once())
			->method('handleException')
			->with($this->isInstanceOf(NativeShutdownException::class))
			->willReturn(null);

		$source
			->expects($this->never())
			->method('_terminate');

		$this->assertNull($source->handleShutdown());
	}
}
