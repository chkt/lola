<?php

namespace test\io\http;

use PHPUnit\Framework\TestCase;

use lola\io\connect\IConnection;
use lola\io\connect\Connection;
use lola\io\http\IHttpMessage;
use lola\io\http\IHttpDriver;
use lola\io\http\HttpClient;
use lola\io\http\HttpMessage;



final class HttpClientTest
extends TestCase
{

	private function _produceConnection(array $data = null) : IConnection {
		if (is_null($data)) $data = [
			'client' => [
				'ip' => '127.0.0.1'
			]
		];

		return new Connection($data);
	}

	private function _produceMessage(array $headers = null) : IHttpMessage {
		if (is_null($headers)) $headers = [
			IHttpMessage::HEADER_DATE => ['Thu, 01 Jan 1970 08:37:42 GMT'],
			IHttpMessage::HEADER_USER_AGENT => ['Mozilla/5.0']
		];

		return new HttpMessage('', $headers);
	}

	private function _mockDriver(IConnection $connection = null, IHttpMessage $message = null) : IHttpDriver {
		if (is_null($connection)) $connection = $this->_produceConnection();
		if (is_null($message)) $message = $this->_produceMessage();

		$driver = $this
			->getMockBuilder(IHttpDriver::class)
			->getMock();

		$driver
			->expects($this->any())
			->method('useConnection')
			->with()
			->willReturnReference($connection);

		$driver
			->expects($this->any())
			->method('useRequestMessage')
			->with()
			->willReturnReference($message);

		return $driver;
	}

	private function _produceClient(IHttpDriver $driver = null) {
		if (is_null($driver)) $driver = $this->_mockDriver();

		return new HttpClient($driver);
	}


	public function testIsIP4() {
		$client = $this->_produceClient();

		$this->assertTrue($client->isIP4());

		$client->setIP('::1');

		$this->assertFalse($client->isIP4());
	}

	public function testIsIP6() {
		$client = $this->_produceClient();

		$this->assertFalse($client->isIP6());

		$client->setIP('::1');

		$this->assertTrue($client->isIP6());
	}

	public function testGetIP() {
		$client = $this->_produceClient();

		$this->assertEquals('127.0.0.1', $client->getIP());
	}

	public function testSetIP() {
		$client = $this->_produceClient();

		$this->assertSame($client, $client->setIP('::1'));
		$this->assertEquals('::1', $client->getIP());
	}

	public function testGetUA() {
		$client = $this->_produceClient();

		$this->assertEquals($client->getUA(), 'Mozilla/5.0');
	}

	public function testSetUA() {
		$client = $this->_produceClient();

		$this->assertSame($client, $client->setUA('foo'));
		$this->assertEquals('foo', $client->getUA());
	}

	public function testGetTime() {
		$client = $this->_produceClient();

		$time = 8 * 3600 + 37 * 60 + 42;

		$this->assertEquals($time, $client->getTime());
	}

	public function testSetTime() {
		$client = $this->_produceClient();

		$this->assertSame($client, $client->setTime(3));
		$this->assertEquals(3, $client->gettime());
	}
}
