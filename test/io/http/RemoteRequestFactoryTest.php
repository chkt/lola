<?php

namespace test\io\http;

use PHPUnit\Framework\TestCase;
use phpmock\phpunit\PHPMock;

use lola\io\http\IHttpMessage;
use lola\io\http\AHttpMessageFactory;
use lola\io\http\RemoteRequestFactory;



final class RemoteRequestFactoryTest
extends TestCase
{
	use PHPMock;


	private function _mock_filter_input() {
		$fn = $this->getFunctionMock('\lola\io\http', 'filter_input');
		$fn
			->expects($this->exactly(3))
			->with($this->equalTo(INPUT_SERVER), $this->logicalOr(
				$this->equalTo('REQUEST_METHOD'),
				$this->equalTo('REQUEST_URI'),
				$this->equalTo('SERVER_PROTOCOL')
			))
			->willReturnCallback(function(int $source, string $prop) {
				switch ($prop) {
					case 'REQUEST_METHOD' : return 'GET';
					case 'REQUEST_URI' : return '/path/to/resource';
					case 'SERVER_PROTOCOL' : return 'HTTP/1.1';
				}
			});
	}

	private function _mock_getallheaders() {
		$fn = $this->getFunctionMock('\lola\io\http', 'getallheaders');
		$fn
			->expects($this->once())
			->with()
			->willReturn([
				'Content-Type' => 'application/json; charset=utf-8',
				'Cookie' => 'foo=bar; baz=quux'
			]);
	}

	private function _mockFileOpertations() {
		$fopen = $this->getFunctionMock('\lola\io\http', 'fopen');
		$fopen
			->expects($this->once())
			->with($this->equalTo('php://input'), $this->equalTo('r'))
			->willReturn(1);

		$streamGetContents = $this->getFunctionMock('\lola\io\http', 'stream_get_contents');
		$streamGetContents
			->expects($this->once())
			->with($this->equalTo(1))
			->willReturn('{"foo":{"bar":"baz"}}');

		$fclose = $this->getFunctionMock('\lola\io\http', 'fclose');
		$fclose
			->expects($this->once())
			->with($this->equalTo(1))
			->willReturn(true);
	}

	private function _produceFactory() {
		return new RemoteRequestFactory();
	}


	public function testInheritance() {
		$factory = $this->_produceFactory();

		$this->assertInstanceOf(AHttpMessageFactory::class, $factory);
	}

	public function testGetMessage() {
		$this->_mock_filter_input();
		$this->_mock_getallheaders();
		$this->_mockFileOpertations();

		$instance = $this
			->_produceFactory()
			->getMessage();

		$this->assertInstanceOf(IHttpMessage::class, $instance);

		$str = "GET /path/to/resource HTTP/1.1\r\n" .
			"Content-Type: application/json; charset=utf-8\r\n" .
			"Cookie: foo=bar; baz=quux\r\n" .
			"\r\n" .
			"{\"foo\":{\"bar\":\"baz\"}}";

		$this->assertEquals($str, (string) $instance);
	}
}
