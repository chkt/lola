<?php

namespace test\error;

use lola\error\NativeShutdownException;
use PHPUnit\Framework\TestCase;

use eve\common\access\TraversableAccessor;
use eve\inject\IInjectable;
use lola\log\ILogger;
use lola\error\IErrorHandler;
use lola\error\BasicErrorHandler;



final class BasicErrorHandlerTest
extends TestCase
{

	private function _mockLog() : ILogger {
		$ins = $this
			->getMockBuilder(ILogger::class)
			->getMock();

		return $ins;
	}


	private function _produceAccessor(array $data = []) {
		return new TraversableAccessor($data);
	}

	private function _produceShutdownException() : NativeShutdownException {
		return new NativeShutdownException(E_ERROR, '', '', 0);
	}

	private function _produceHandler(ILogger $logger = null) : BasicErrorHandler {
		if (is_null($logger)) $logger = $this->_mockLog();

		return new BasicErrorHandler($logger);
	}


	public function testInheritance() {
		$handler = $this->_produceHandler();

		$this->assertInstanceOf(IErrorHandler::class, $handler);
		$this->assertInstanceOf(IInjectable::class, $handler);
	}


	public function testDependencyConfig() {
		$this->assertEquals([
			'environment:log'
		], BasicErrorHandler::getDependencyConfig($this->_produceAccessor()));
	}


	public function testHandleException() {
		$logger = $this->_mockLog();
		$handler = $this->_produceHandler($logger);

		$logger
			->expects($this->once())
			->method('logException')
			->with($this->isInstanceOf(\Error::class))
			->willReturnCallback(function(\Throwable $ex) use ($logger) {
				$this->assertEquals('foo', $ex->getMessage());
				$this->assertEquals(1, $ex->getCode());

				return $logger;
			});

		$handler->handleException(new \Error('foo', 1));
	}

	public function testHandleException_shutdown() {
		$logger = $this->_mockLog();
		$handler = $this->_produceHandler($logger);

		$logger
			->expects($this->never())
			->method('logException');

		$handler->handleException($this->_produceShutdownException());
	}
}
