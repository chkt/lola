<?php

namespace test\io\http;

use PHPUnit\Framework\TestCase;

use lola\io\http\IHttpMessageFactory;
use lola\io\http\IHttpMessage;
use lola\io\http\AHttpMessageFactory;
use lola\io\http\HttpMessage;



final class AHttpMessageFactoryTest
extends TestCase
{

	private function _mockFactory() : IHttpMessageFactory {
		$res = $this->getMockBuilder(AHttpMessageFactory::class)
			->getMockForAbstractClass();

		$res
			->expects($this->any())
			->method('_produceInstance')
			->with()
			->willReturnCallback(function() {
				return new HttpMessage('foo', [], 'bar');
			});

		return $res;
	}


	public function testInheritance() {
		$factory = $this->_mockFactory();

		$this->assertInstanceOf(IHttpMessageFactory::class, $factory);
	}


	public function testDependencyConfig() {
		$this->assertEquals([], AHttpMessageFactory::getDependencyConfig([]));
	}


	public function testGetMessage() {
		$factory = $this->_mockFactory();
		$message = $factory->getMessage();

		$this->assertInstanceOf(IHttpMessage::class, $message);
		$this->assertSame($message, $factory->getMessage());
	}
}
