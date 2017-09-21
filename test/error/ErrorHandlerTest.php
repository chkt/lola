<?php

namespace test\error;

use eve\inject\IInjectableIdentity;
use PHPUnit\Framework\TestCase;

use eve\access\TraversableAccessor;
use lola\log\ILogger;
use lola\error\IErrorHandler;
use lola\error\ErrorHandler;



final class ErrorHandlerTest
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

	private function _produceHandler(ILogger $logger = null) : ErrorHandler {
		if (is_null($logger)) $logger = $this->_mockLog();

		return new ErrorHandler($logger);
	}


	public function testInheritance() {
		$handler = $this->_produceHandler();

		$this->assertInstanceOf(IErrorHandler::class, $handler);
	}


	public function testDependencyConfig() {
		$this->assertEquals([
			'environment:log'
		], ErrorHandler::getDependencyConfig($this->_produceAccessor()));
	}

	public function testInstanceIdentity() {
		$this->assertEquals(IInjectableIdentity::IDENTITY_SINGLE, ErrorHandler::getInstanceIdentity($this->_produceAccessor()));
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

	public function testHandleError() {
		$logger = $this->_mockLog();
		$handler = $this->_produceHandler($logger);

		$logger
			->expects($this->exactly(15))
			->method('logError')
			->with($this->isType('array'))
			->willReturnCallback(function(array $error) use ($logger) {
				$this->assertEquals('foo', $error['message']);
				$this->assertEquals('path/to/file.php', $error['file']);
				$this->assertEquals(2, $error['line']);

				return $logger;
			});

		$props = [
			'message' => 'foo',
			'file' => 'path/to/file.php',
			'line' => 2
		];

		$this->assertFalse($handler->handleError(array_merge(['type' => E_ERROR], $props)));
		$this->assertTrue($handler->handleError(array_merge(['type' => E_WARNING], $props)));
		$this->assertFalse($handler->handleError(array_merge(['type' => E_PARSE], $props)));
		$this->assertTrue($handler->handleError(array_merge(['type' => E_NOTICE], $props)));

		$this->assertFalse($handler->handleError(array_merge(['type' => E_CORE_ERROR], $props)));
		$this->assertTrue($handler->handleError(array_merge(['type' => E_CORE_WARNING], $props)));
		$this->assertFalse($handler->handleError(array_merge(['type' => E_COMPILE_ERROR], $props)));
		$this->assertTrue($handler->handleError(array_merge(['type' => E_COMPILE_WARNING], $props)));

		$this->assertFalse($handler->handleError(array_merge(['type' => E_USER_ERROR], $props)));
		$this->assertTrue($handler->handleError(array_merge(['type' => E_USER_WARNING], $props)));
		$this->assertTrue($handler->handleError(array_merge(['type' => E_USER_NOTICE], $props)));
		$this->assertTrue($handler->handleError(array_merge(['type' => E_STRICT], $props)));

		$this->assertTrue($handler->handleError(array_merge(['type' => E_RECOVERABLE_ERROR], $props)));
		$this->assertTrue($handler->handleError(array_merge(['type' => E_DEPRECATED], $props)));
		$this->assertTrue($handler->handleError(array_merge(['type' => E_USER_DEPRECATED], $props)));
	}

	public function testHandleShutdownError() {
		$logger = $this->_mockLog();
		$handler = $this->_produceHandler($logger);

		$logger
			->expects($this->never())
			->method('logError');

		$handler->handleShutdownError([
			'type' => E_ERROR,
			'message' => 'foo',
			'file' => '/path/to/file.php',
			'line' => 2
		]);
	}
}
