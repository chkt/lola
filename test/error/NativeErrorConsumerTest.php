<?php

namespace test\error;

use PHPUnit\Framework\TestCase;
use phpmock\phpunit\PHPMock;

use eve\provide\ILocator;
use lola\common\INativeConsumer;
use lola\error\IErrorHandler;
use lola\error\NativeErrorConsumer;



final class NativeErrorConsumerTest
extends TestCase
{

	use PHPMock;


	private function _mockHandler() : IErrorHandler {
		$ins = $this
			->getMockBuilder(IErrorHandler::class)
			->getMock();

		return $ins;
	}

	private function _mockLocator(IErrorHandler $handler = null) : ILocator {
		if (is_null($handler)) $handler = $this->_mockHandler();

		$ins = $this
			->getMockBuilder(ILocator::class)
			->getMock();

		$ins
			->expects($this->any())
			->method('locate')
			->with($this->equalTo('environment:errors'))
			->willReturn($handler);

		return $ins;
	}


	private function _mockConsumer(ILocator $locator = null) : NativeErrorConsumer {
		if (is_null($locator)) $locator = $this->_mockLocator();

		$ins = $this
			->getMockBuilder(NativeErrorConsumer::class)
			->setConstructorArgs([ $locator ])
			->setMethods([ '_terminate' ])
			->getMock();

		$ins
			->expects($this->any())
			->method('_terminate')
			->with()
			->willReturnCallback(function() {
				throw new \Exception('terminated');
			});

		return $ins;
	}


	public function testInheritance() {
		$ins = $this->_mockConsumer();

		$this->assertInstanceOf(INativeConsumer::class, $ins);
	}


	public function testConsumeException() {
		$handler = $this->_mockHandler();
		$locator = $this->_mockLocator($handler);
		$ins = $this->_mockConsumer($locator);

		$handler
			->expects($this->once())
			->method('handleException')
			->with($this->isInstanceOf(\Throwable::class))
			->willReturnCallback(function(\Throwable $ex) {
				$this->assertEquals('Foo is not a valid concept', $ex->getMessage());
				$this->assertEquals(23, $ex->getCode());
			});

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('terminated');

		$ins->consumeException(new \ErrorException('Foo is not a valid concept', 23));
	}

	public function testConsumeError() {
		$handler = $this->_mockHandler();
		$locator = $this->_mockLocator($handler);
		$ins = $this->_mockConsumer($locator);

		$handler
			->expects($this->at(0))
			->method('handleError')
			->with($this->isType('array'))
			->willReturnCallback(function(array $error) {
				$this->assertArrayHasKey('type', $error);
				$this->assertEquals(E_WARNING, $error['type']);
				$this->assertArrayHasKey('message', $error);
				$this->assertEquals('foo', $error['message']);
				$this->assertArrayHasKey('file', $error);
				$this->assertEquals('/path/to/file.php', $error['file']);
				$this->assertArrayHasKey('line', $error);
				$this->assertEquals(23, $error['line']);

				return true;
			});

		$handler
			->expects($this->at(1))
			->method('handleError')
			->with($this->isType('array'))
			->willReturnCallback(function(array $error) {
				return false;
			});

		$this->assertTrue($ins->consumeError(E_WARNING, 'foo', '/path/to/file.php', 23));

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('terminated');

		$ins->consumeError(E_ERROR, 'bar', '/path/to/file.php', 5);
	}

	public function testConsumeShutdownError() {
		$fn = $this->getFunctionMock('\lola\error', 'error_get_last');

		$fn
			->expects($this->once())
			->with()
			->willReturnCallback(function() {
				return [
					'type' => E_WARNING,
					'message' => 'foo',
					'file' => '/path/to/file.php',
					'line' => 23
				];
			});

		$handler = $this->_mockHandler();
		$locator = $this->_mockLocator($handler);
		$ins = $this->_mockConsumer($locator);

		$handler
			->expects($this->once())
			->method('handleShutdownError')
			->with($this->isType('array'))
			->willReturnCallback(function(array $error) {
				$this->assertArrayHasKey('type', $error);
				$this->assertEquals(E_WARNING, $error['type']);
				$this->assertArrayHasKey('message', $error);
				$this->assertEquals('foo', $error['message']);
				$this->assertArrayHasKey('file', $error);
				$this->assertEquals('/path/to/file.php', $error['file']);
				$this->assertArrayHasKey('line', $error);
				$this->assertEquals(23, $error['line']);
			});

		$ins->consumeShutdownError();
	}


	public function testAttach() {
		$this
			->getFunctionMock('\lola\error', 'set_exception_handler')
			->expects($this->once())
			->with($this->isType('callable'))
			->willReturn(null);

		$this
			->getFunctionMock('\lola\error', 'set_error_handler')
			->expects($this->once())
			->with($this->isType('callable'))
			->willReturn(null);

		$this
			->getFunctionMock('\lola\error', 'register_shutdown_function')
			->expects($this->once())
			->with($this->isType('callable'))
			->willReturn(null);

		$ins = $this->_mockConsumer();

		$this->assertSame($ins, $ins->attach());
	}
}
