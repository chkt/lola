<?php

namespace test\io\http;

use PHPUnit\Framework\TestCase;

use lola\io\http\IHttpMessage;
use lola\io\http\AHttpMessageFactory;
use lola\io\http\RemoteReplyFactory;



final class RemoteReplyFactoryTest
extends TestCase
{

	private function _produceFactory() {
		return new RemoteReplyFactory();
	}


	public function testInheritance() {
		$factory = $this->_produceFactory();

		$this->assertInstanceOf(AHttpMessageFactory::class, $factory);
	}

	public function testGetMessage() {
		$instance = $this
			->_produceFactory()
			->getMessage();

		$this->assertInstanceOf(IHttpMessage::class, $instance);

		$str = "HTTP/1.1 200 OK\r\n" .
			"Content-Type: text/plain;charset=utf-8\r\n" .
			"\r\n";

		$this->assertEquals($str, (string) $instance);
	}
}
